<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's brands.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Levi\'s',
                'slug' => 'levis',
                'description' => 'Classic denim and casual essentials.',
                'is_active' => true,
            ],
            [
                'name' => 'Uniqlo',
                'slug' => 'uniqlo',
                'description' => 'Minimal everyday wear and basics.',
                'is_active' => true,
            ],
            [
                'name' => 'Zara',
                'slug' => 'zara',
                'description' => 'Trend-focused modern pieces.',
                'is_active' => true,
            ],
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Sportswear and performance footwear.',
                'is_active' => true,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Athletic apparel and street styles.',
                'is_active' => true,
            ],
            [
                'name' => 'No Brand',
                'slug' => 'no-brand',
                'description' => 'Quality thrift finds without brand label.',
                'is_active' => true,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }
    }
}
