<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function products(Request $request)
    {
        $query = Product::query()
            ->published()
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'images:id,product_id,path,alt_text,sort_order,is_primary',
            ])
            ->withReservedQuantity();

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('available_only')) {
            $query->whereRaw('(stock_quantity - COALESCE(reserved_quantity, 0)) > 0');
        }

        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest('id'),
        };

        $perPage = min(max((int) $request->input('per_page', 24), 1), 60);

        return ProductResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function showProduct(Product $product)
    {
        abort_unless($product->status === Product::STATUS_PUBLISHED, 404);

        $product->load([
            'category:id,name,slug',
            'brand:id,name,slug',
            'images:id,product_id,path,alt_text,sort_order,is_primary',
        ]);

        $product->loadReservedQuantity();

        return new ProductResource($product);
    }

    public function categories()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function brands()
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }
}
