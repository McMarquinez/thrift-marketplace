<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function getReservedQuantity(int $productId, ?int $excludeOrderId = null): int
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', Order::STATUS_PENDING_PAYMENT)
            ->where(function ($subQuery) {
                $subQuery
                    ->whereNull('orders.expires_at')
                    ->orWhere('orders.expires_at', '>', now());
            });

        if ($excludeOrderId !== null) {
            $query->where('orders.id', '!=', $excludeOrderId);
        }

        return (int) ($query->sum('order_items.quantity') ?? 0);
    }

    public function getAvailableQuantity(Product $product, ?int $excludeOrderId = null): int
    {
        $reserved = $this->getReservedQuantity((int) $product->id, $excludeOrderId);

        return max(0, (int) $product->stock_quantity - $reserved);
    }

    public function assertStockAvailable(Product $product, int $requestedQuantity, ?int $excludeOrderId = null): void
    {
        $available = $this->getAvailableQuantity($product, $excludeOrderId);

        if ($requestedQuantity > $available) {
            throw new InsufficientStockException("Insufficient stock for SKU {$product->sku}. Requested {$requestedQuantity}, available {$available}.");
        }
    }

    /**
     * Validate that each line item can be reserved.
     *
     * @param array<int, array{product_id:int, quantity:int}> $lineItems
     */
    public function validateReservationItems(array $lineItems): void
    {
        DB::transaction(function () use ($lineItems) {
            foreach ($lineItems as $lineItem) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->find($lineItem['product_id']);

                if (! $product) {
                    throw (new ModelNotFoundException())->setModel(Product::class, [$lineItem['product_id']]);
                }

                $this->assertStockAvailable($product, (int) $lineItem['quantity']);
            }
        });
    }

    /**
     * Finalize inventory for a paid order by deducting stock exactly once.
     */
    public function finalizePaidOrderStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($order->id);

            // Idempotency: do not deduct stock twice.
            if ($lockedOrder->status === Order::STATUS_PAID) {
                return;
            }

            foreach ($lockedOrder->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock_quantity < $item->quantity) {
                    throw new InsufficientStockException("Stock finalization failed for SKU {$product->sku}. Needed {$item->quantity}, in stock {$product->stock_quantity}.");
                }

                $product->decrement('stock_quantity', (int) $item->quantity);
            }

            $lockedOrder->update([
                'status' => Order::STATUS_PAID,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        });
    }

    /**
     * Release reservation for an unpaid order.
     *
     * Since reservation is derived from pending orders, releasing only needs a status change.
     */
    public function releaseReservation(Order $order, string $nextStatus = Order::STATUS_CANCELLED): void
    {
        if (! in_array($nextStatus, [Order::STATUS_CANCELLED, Order::STATUS_EXPIRED], true)) {
            return;
        }

        $order->update([
            'status' => $nextStatus,
            'payment_status' => $nextStatus === Order::STATUS_EXPIRED ? 'expired' : $order->payment_status,
            'cancelled_at' => $nextStatus === Order::STATUS_CANCELLED ? now() : $order->cancelled_at,
        ]);
    }

    public function expirePendingOrders(?Carbon $asOf = null): int
    {
        $asOf = $asOf ?? now();

        $orders = Order::query()
            ->pendingPayment()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $asOf)
            ->get();

        $expired = 0;

        foreach ($orders as $order) {
            $this->releaseReservation($order, Order::STATUS_EXPIRED);
            $expired++;
        }

        return $expired;
    }
}
