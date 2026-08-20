<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createOrder(array $payload): Order
    {
        return DB::transaction(function () use ($payload) {
            $normalizedItems = $this->normalizeItems($payload['items']);
            $products = $this->lockAndLoadProducts(array_keys($normalizedItems));

            $subtotal = 0.0;
            $orderItems = [];

            foreach ($normalizedItems as $productId => $quantity) {
                $product = $products[$productId] ?? null;

                if (! $product) {
                    throw (new ModelNotFoundException())->setModel(Product::class, [$productId]);
                }

                if ($product->status !== Product::STATUS_PUBLISHED) {
                    throw new InsufficientStockException("Product {$product->sku} is not available for checkout.");
                }

                $this->inventoryService->assertStockAvailable($product, $quantity);

                $lineSubtotal = (float) $product->price * $quantity;
                $subtotal += $lineSubtotal;

                $orderItems[] = [
                    'product_id' => (int) $product->id,
                    'product_name' => (string) $product->name,
                    'sku' => (string) $product->sku,
                    'unit_price' => (float) $product->price,
                    'quantity' => (int) $quantity,
                    'subtotal' => round($lineSubtotal, 2),
                ];
            }

            $shippingFee = $this->getNumericSetting('shipping_fee', 0);
            $discountAmount = 0.0;
            $total = max(0, round($subtotal + $shippingFee - $discountAmount, 2));

            $customer = $this->findOrCreateCustomer($payload);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => round($subtotal, 2),
                'shipping_fee' => round($shippingFee, 2),
                'discount_amount' => round($discountAmount, 2),
                'total' => round($total, 2),
                'payment_status' => Payment::STATUS_PENDING,
                'shipping_status' => 'pending',
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'] ?? null,
                'shipping_address' => $payload['shipping_address'],
                'notes' => $payload['notes'] ?? null,
                'expires_at' => now()->addMinutes((int) $this->getNumericSetting('payment_window_minutes', 30)),
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            $order->payments()->create([
                'reference_number' => null,
                'provider' => null,
                'method' => (string) $payload['payment_method'],
                'amount' => round($total, 2),
                'status' => Payment::STATUS_PENDING,
                'metadata' => ['source' => 'checkout'],
            ]);

            return $order->fresh(['items', 'payments']);
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function applyPaymentUpdate(Order $order, array $payload, bool $fromWebhook = false): Order
    {
        return DB::transaction(function () use ($order, $payload, $fromWebhook) {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->with(['items', 'payments'])
                ->findOrFail($order->id);

            if ($lockedOrder->status === Order::STATUS_PAID && ($payload['status'] ?? null) === Payment::STATUS_PAID) {
                return $lockedOrder;
            }

            $payment = $lockedOrder->payments()->latest('id')->first();

            if (! $payment) {
                $payment = $lockedOrder->payments()->create([
                    'method' => (string) ($payload['method'] ?? 'manual'),
                    'amount' => (float) ($payload['amount'] ?? $lockedOrder->total),
                    'status' => Payment::STATUS_PENDING,
                ]);
            }

            $metadata = is_array($payment->metadata) ? $payment->metadata : [];

            if ($fromWebhook && ! empty($payload['event_id'])) {
                $lastEventId = (string) ($metadata['last_event_id'] ?? '');
                if ($lastEventId !== '' && $lastEventId === (string) $payload['event_id']) {
                    return $lockedOrder;
                }
                $metadata['last_event_id'] = (string) $payload['event_id'];
            }

            if (isset($payload['metadata']) && is_array($payload['metadata'])) {
                $metadata = array_merge($metadata, $payload['metadata']);
            }

            $payment->update([
                'reference_number' => $payload['reference_number'] ?? $payment->reference_number,
                'provider' => $payload['provider'] ?? $payment->provider,
                'method' => $payload['method'] ?? $payment->method,
                'amount' => isset($payload['amount']) ? (float) $payload['amount'] : $payment->amount,
                'status' => $payload['status'],
                'paid_at' => $payload['status'] === Payment::STATUS_PAID ? now() : $payment->paid_at,
                'metadata' => $metadata,
            ]);

            if ($payload['status'] === Payment::STATUS_PAID) {
                $this->inventoryService->finalizePaidOrderStock($lockedOrder);
            }

            if ($payload['status'] === Payment::STATUS_FAILED) {
                $lockedOrder->update([
                    'payment_status' => Payment::STATUS_FAILED,
                ]);
            }

            return $lockedOrder->fresh(['items', 'payments']);
        });
    }

    /**
     * @param array<int, array{product_id:int, quantity:int}> $items
     * @return array<int, int>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            if (! isset($normalized[$productId])) {
                $normalized[$productId] = 0;
            }

            $normalized[$productId] += $quantity;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array<int, int> $productIds
     * @return array<int, Product>
     */
    private function lockAndLoadProducts(array $productIds): array
    {
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $mapped = [];
        foreach ($products as $product) {
            $mapped[(int) $product->id] = $product;
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function findOrCreateCustomer(array $payload): Customer
    {
        $email = $payload['customer_email'];

        $attributes = [
            'name' => $payload['customer_name'],
            'phone' => $payload['customer_phone'] ?? null,
            'address_line_1' => $payload['shipping_address'],
        ];

        return Customer::query()->updateOrCreate(
            ['email' => $email],
            $attributes
        );
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'TM-' . now()->format('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function getNumericSetting(string $key, float|int $default = 0): float
    {
        $raw = Setting::query()
            ->where('key', $key)
            ->value('value');

        if ($raw === null || $raw === '') {
            return (float) $default;
        }

        return is_numeric($raw) ? (float) $raw : (float) $default;
    }
}
