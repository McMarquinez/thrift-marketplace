<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InventoryPhaseSixTest extends TestCase
{
    use RefreshDatabase;

    private static int $categoryCounter = 0;
    private static int $customerCounter = 0;

    private function makeCategory(): Category
    {
        self::$categoryCounter++;

        return Category::query()->create([
            'name' => 'Clothing '.self::$categoryCounter,
            'slug' => 'clothing-'.self::$categoryCounter,
            'is_active' => true,
        ]);
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = $this->makeCategory();

        return Product::query()->create(array_merge([
            'sku' => 'TS-000001',
            'name' => 'Vintage Shirt',
            'slug' => 'vintage-shirt',
            'price' => 1000,
            'stock_quantity' => 5,
            'condition' => 'good',
            'status' => Product::STATUS_PUBLISHED,
            'category_id' => $category->id,
            'brand_id' => null,
        ], $overrides));
    }

    private function makeCustomer(): Customer
    {
        self::$customerCounter++;

        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer'.self::$customerCounter.'@example.com',
            'phone' => '09171234567',
        ]);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $customer = $this->makeCustomer();

        return Order::query()->create(array_merge([
            'order_number' => 'TS-ORDER-0001',
            'customer_id' => $customer->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 1000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total' => 1000,
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '09171234567',
            'shipping_address' => 'Sample Address',
            'expires_at' => now()->addMinutes(30),
        ], $overrides));
    }

    public function test_reserved_quantity_counts_only_active_pending_orders(): void
    {
        $service = app(InventoryService::class);
        $product = $this->makeProduct();

        $activeOrder = $this->makeOrder([
            'order_number' => 'TS-ORDER-1001',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'expires_at' => now()->addMinutes(30),
        ]);
        OrderItem::query()->create([
            'order_id' => $activeOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 2,
            'subtotal' => 2000,
        ]);

        $expiredOrder = $this->makeOrder([
            'order_number' => 'TS-ORDER-1002',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'expires_at' => now()->subMinute(),
        ]);
        OrderItem::query()->create([
            'order_id' => $expiredOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        $paidOrder = $this->makeOrder([
            'order_number' => 'TS-ORDER-1003',
            'status' => Order::STATUS_PAID,
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);
        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        $this->assertSame(2, $service->getReservedQuantity($product->id));
        $this->assertSame(3, $service->getAvailableQuantity($product));
    }

    public function test_assert_stock_available_throws_when_requested_exceeds_available(): void
    {
        $service = app(InventoryService::class);
        $product = $this->makeProduct(['stock_quantity' => 1]);

        $reservationOrder = $this->makeOrder([
            'order_number' => 'TS-ORDER-2001',
            'expires_at' => now()->addMinutes(15),
        ]);
        OrderItem::query()->create([
            'order_id' => $reservationOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        $this->expectException(InsufficientStockException::class);
        $service->assertStockAvailable($product, 1);
    }

    public function test_finalize_paid_order_stock_deducts_once_and_marks_order_paid(): void
    {
        $service = app(InventoryService::class);
        $product = $this->makeProduct([
            'sku' => 'TS-000010',
            'slug' => 'vintage-jacket',
            'stock_quantity' => 4,
        ]);

        $order = $this->makeOrder([
            'order_number' => 'TS-ORDER-3001',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 1000,
            'quantity' => 3,
            'subtotal' => 3000,
        ]);

        $service->finalizePaidOrderStock($order);

        $product->refresh();
        $order->refresh();

        $this->assertSame(1, $product->stock_quantity);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);

        $service->finalizePaidOrderStock($order);
        $product->refresh();

        $this->assertSame(1, $product->stock_quantity);
    }

    public function test_expire_pending_orders_releases_reservation_via_status_change(): void
    {
        Carbon::setTestNow('2026-08-20 10:00:00');

        $service = app(InventoryService::class);

        $expired = $this->makeOrder([
            'order_number' => 'TS-ORDER-4001',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
            'expires_at' => now()->subMinutes(5),
        ]);

        $stillActive = $this->makeOrder([
            'order_number' => 'TS-ORDER-4002',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $count = $service->expirePendingOrders(now());

        $expired->refresh();
        $stillActive->refresh();

        $this->assertSame(1, $count);
        $this->assertSame(Order::STATUS_EXPIRED, $expired->status);
        $this->assertSame('expired', $expired->payment_status);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $stillActive->status);

        Carbon::setTestNow();
    }
}
