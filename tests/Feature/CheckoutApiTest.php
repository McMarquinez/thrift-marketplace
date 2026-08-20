<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_and_reservation(): void
    {
        $product = $this->createPublishedProduct(5, 299.99);

        Setting::query()->updateOrCreate(['key' => 'shipping_fee'], [
            'value' => '50',
            'type' => 'integer',
        ]);

        Setting::query()->updateOrCreate(['key' => 'payment_window_minutes'], [
            'value' => '45',
            'type' => 'integer',
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Jane Customer',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '09171234567',
            'shipping_address' => 'Blk 3 Lot 9 Sample Street',
            'payment_method' => 'bank_transfer',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', Order::STATUS_PENDING_PAYMENT)
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonPath('data.pricing.subtotal', 599.98)
            ->assertJsonPath('data.pricing.shipping_fee', 50)
            ->assertJsonPath('data.pricing.total', 649.98);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_checkout_rejects_unavailable_quantity(): void
    {
        $product = $this->createPublishedProduct(1, 100);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Jane Customer',
            'customer_email' => 'jane2@example.com',
            'shipping_address' => 'Sample Address',
            'payment_method' => 'gcash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'error']);
    }

    public function test_payment_confirmation_is_idempotent_for_stock_deduction(): void
    {
        $product = $this->createPublishedProduct(3, 120);

        $orderResponse = $this->postJson('/api/orders', [
            'customer_name' => 'Repeat Customer',
            'customer_email' => 'repeat@example.com',
            'shipping_address' => 'Address 1',
            'payment_method' => 'manual',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertCreated();

        $orderNumber = $orderResponse->json('data.order_number');

        $payload = [
            'status' => 'paid',
            'reference_number' => 'REF-001',
            'provider' => 'manual',
            'method' => 'manual',
        ];

        $this->postJson("/api/orders/{$orderNumber}/payment", $payload)
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_PAID);

        $this->postJson("/api/orders/{$orderNumber}/payment", $payload)
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_PAID);

        $product->refresh();
        $this->assertSame(1, (int) $product->stock_quantity);
    }

    public function test_order_tracking_requires_matching_email(): void
    {
        $product = $this->createPublishedProduct(2, 80);

        $orderResponse = $this->postJson('/api/orders', [
            'customer_name' => 'Tracker',
            'customer_email' => 'tracker@example.com',
            'shipping_address' => 'Address 1',
            'payment_method' => 'manual',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $orderNumber = $orderResponse->json('data.order_number');

        $this->postJson('/api/orders/track', [
            'order_number' => $orderNumber,
            'email' => 'tracker@example.com',
        ])->assertOk();

        $this->postJson('/api/orders/track', [
            'order_number' => $orderNumber,
            'email' => 'wrong@example.com',
        ])->assertNotFound();
    }

    private function createPublishedProduct(int $stock, float $price): Product
    {
        $category = Category::query()->create([
            'name' => 'Category A',
            'slug' => 'category-a-' . uniqid(),
            'is_active' => true,
        ]);

        $brand = Brand::query()->create([
            'name' => 'Brand A',
            'slug' => 'brand-a-' . uniqid(),
            'is_active' => true,
        ]);

        return Product::query()->create([
            'sku' => 'SKU-' . uniqid(),
            'name' => 'Sample Product',
            'slug' => 'sample-product-' . uniqid(),
            'price' => $price,
            'stock_quantity' => $stock,
            'condition' => 'good',
            'status' => 'published',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
    }
}
