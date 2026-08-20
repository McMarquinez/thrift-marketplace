<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's products.
     */
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'slug');
        $brandIds = Brand::query()->pluck('id', 'slug');

        $products = [
            [
                'sku' => 'TS-100001',
                'name' => 'Vintage Denim Jacket',
                'slug' => 'vintage-denim-jacket',
                'description' => 'A classic vintage denim jacket with clean fading and sturdy buttons.',
                'short_description' => 'Classic vintage denim jacket.',
                'price' => 850,
                'compare_at_price' => 1200,
                'cost_price' => 400,
                'stock_quantity' => 1,
                'condition' => 'very_good',
                'status' => 'published',
                'category_slug' => 'women',
                'brand_slug' => 'levis',
            ],
            [
                'sku' => 'TS-100002',
                'name' => 'Relaxed Fit Denim Shirt',
                'slug' => 'relaxed-fit-denim-shirt',
                'description' => 'Soft-washed denim shirt with relaxed silhouette for daily wear.',
                'short_description' => 'Soft-washed relaxed denim shirt.',
                'price' => 1200,
                'compare_at_price' => 1600,
                'cost_price' => 520,
                'stock_quantity' => 2,
                'condition' => 'like_new',
                'status' => 'published',
                'category_slug' => 'men',
                'brand_slug' => 'uniqlo',
            ],
            [
                'sku' => 'TS-100003',
                'name' => 'Y2K Shoulder Bag',
                'slug' => 'y2k-shoulder-bag',
                'description' => 'Compact shoulder bag with a Y2K silhouette and zipper closure.',
                'short_description' => 'Compact Y2K shoulder bag.',
                'price' => 450,
                'compare_at_price' => 700,
                'cost_price' => 210,
                'stock_quantity' => 1,
                'condition' => 'very_good',
                'status' => 'published',
                'category_slug' => 'accessories',
                'brand_slug' => 'no-brand',
            ],
            [
                'sku' => 'TS-100004',
                'name' => 'Pleated Midi Skirt',
                'slug' => 'pleated-midi-skirt',
                'description' => 'Flowy pleated midi skirt that pairs well with basics or blouses.',
                'short_description' => 'Flowy pleated midi skirt.',
                'price' => 680,
                'compare_at_price' => 980,
                'cost_price' => 300,
                'stock_quantity' => 3,
                'condition' => 'good',
                'status' => 'published',
                'category_slug' => 'women',
                'brand_slug' => 'zara',
            ],
            [
                'sku' => 'TS-100005',
                'name' => 'Oversized Graphic Tee',
                'slug' => 'oversized-graphic-tee',
                'description' => 'Breathable cotton tee with vintage-style print and oversized cut.',
                'short_description' => 'Cotton oversized graphic tee.',
                'price' => 390,
                'compare_at_price' => 620,
                'cost_price' => 150,
                'stock_quantity' => 5,
                'condition' => 'good',
                'status' => 'published',
                'category_slug' => 'men',
                'brand_slug' => 'no-brand',
            ],
            [
                'sku' => 'TS-100006',
                'name' => 'Kids Hoodie Set',
                'slug' => 'kids-hoodie-set',
                'description' => 'Matching kids hoodie and jogger set with soft inner lining.',
                'short_description' => 'Matching kids hoodie set.',
                'price' => 520,
                'compare_at_price' => 780,
                'cost_price' => 240,
                'stock_quantity' => 4,
                'condition' => 'like_new',
                'status' => 'published',
                'category_slug' => 'kids',
                'brand_slug' => 'adidas',
            ],
            [
                'sku' => 'TS-100007',
                'name' => 'Retro Running Shoes',
                'slug' => 'retro-running-shoes',
                'description' => 'Lightweight retro sneakers with cushioned sole and clean upper.',
                'short_description' => 'Lightweight retro running shoes.',
                'price' => 1400,
                'compare_at_price' => 2200,
                'cost_price' => 780,
                'stock_quantity' => 2,
                'condition' => 'very_good',
                'status' => 'published',
                'category_slug' => 'footwear',
                'brand_slug' => 'nike',
            ],
            [
                'sku' => 'TS-100008',
                'name' => 'Canvas Tote Bag',
                'slug' => 'canvas-tote-bag',
                'description' => 'Durable canvas tote with inner pocket for daily essentials.',
                'short_description' => 'Durable canvas tote bag.',
                'price' => 320,
                'compare_at_price' => 520,
                'cost_price' => 120,
                'stock_quantity' => 6,
                'condition' => 'new',
                'status' => 'published',
                'category_slug' => 'accessories',
                'brand_slug' => 'no-brand',
            ],
            [
                'sku' => 'TS-100009',
                'name' => 'Minimalist Cardigan',
                'slug' => 'minimalist-cardigan',
                'description' => 'Neutral cardigan with clean lines for layering in any season.',
                'short_description' => 'Neutral minimalist cardigan.',
                'price' => 760,
                'compare_at_price' => 1100,
                'cost_price' => 360,
                'stock_quantity' => 2,
                'condition' => 'like_new',
                'status' => 'published',
                'category_slug' => 'women',
                'brand_slug' => 'uniqlo',
            ],
            [
                'sku' => 'TS-100010',
                'name' => 'Streetwear Cargo Pants',
                'slug' => 'streetwear-cargo-pants',
                'description' => 'Multi-pocket cargo pants with tapered fit and sturdy fabric.',
                'short_description' => 'Multi-pocket streetwear cargo pants.',
                'price' => 980,
                'compare_at_price' => 1450,
                'cost_price' => 460,
                'stock_quantity' => 3,
                'condition' => 'very_good',
                'status' => 'published',
                'category_slug' => 'men',
                'brand_slug' => 'zara',
            ],
            [
                'sku' => 'TS-100011',
                'name' => 'Beaded Mini Purse',
                'slug' => 'beaded-mini-purse',
                'description' => 'Statement mini purse with beadwork and magnetic closure.',
                'short_description' => 'Beaded mini purse.',
                'price' => 560,
                'compare_at_price' => 820,
                'cost_price' => 260,
                'stock_quantity' => 1,
                'condition' => 'good',
                'status' => 'published',
                'category_slug' => 'accessories',
                'brand_slug' => 'no-brand',
            ],
            [
                'sku' => 'TS-100012',
                'name' => 'Slip-On Canvas Sneakers',
                'slug' => 'slip-on-canvas-sneakers',
                'description' => 'Easy slip-on sneakers suitable for casual everyday wear.',
                'short_description' => 'Easy slip-on canvas sneakers.',
                'price' => 690,
                'compare_at_price' => 980,
                'cost_price' => 320,
                'stock_quantity' => 4,
                'condition' => 'good',
                'status' => 'published',
                'category_slug' => 'footwear',
                'brand_slug' => 'adidas',
            ],
        ];

        foreach ($products as $product) {
            $categoryId = $categoryIds[$product['category_slug']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $brandId = $brandIds[$product['brand_slug']] ?? null;

            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'short_description' => $product['short_description'],
                    'price' => $product['price'],
                    'compare_at_price' => $product['compare_at_price'],
                    'cost_price' => $product['cost_price'],
                    'stock_quantity' => $product['stock_quantity'],
                    'condition' => $product['condition'],
                    'status' => $product['status'],
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                ]
            );
        }
    }
}
