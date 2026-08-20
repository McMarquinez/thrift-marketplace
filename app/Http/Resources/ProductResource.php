<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryImage = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price !== null ? (float) $this->compare_at_price : null,
            'stock_quantity' => (int) $this->stock_quantity,
            'reserved_quantity' => (int) ($this->reserved_quantity ?? 0),
            'available_quantity' => max(0, (int) $this->stock_quantity - (int) ($this->reserved_quantity ?? 0)),
            'condition' => $this->condition,
            'status' => $this->status,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ]),
            'primary_image' => $primaryImage ? [
                'id' => $primaryImage->id,
                'path' => $primaryImage->path,
                'alt_text' => $primaryImage->alt_text,
            ] : null,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => $image->path,
                'alt_text' => $image->alt_text,
                'sort_order' => (int) $image->sort_order,
                'is_primary' => (bool) $image->is_primary,
            ])->values()),
        ];
    }
}
