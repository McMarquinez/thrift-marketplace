<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_at_price',
        'cost_price',
        'stock_quantity',
        'condition',
        'status',
        'category_id',
        'brand_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeWithReservedQuantity(Builder $query): Builder
    {
        return $query->addSelect([
            'reserved_quantity' => DB::table('order_items')
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereColumn('order_items.product_id', 'products.id')
                ->where('orders.status', Order::STATUS_PENDING_PAYMENT)
                ->where(function ($subQuery) {
                    $subQuery
                        ->whereNull('orders.expires_at')
                        ->orWhere('orders.expires_at', '>', now());
                }),
        ]);
    }

    public function loadReservedQuantity(): self
    {
        $reserved = $this->orderItems()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Order::STATUS_PENDING_PAYMENT)
            ->where(function ($subQuery) {
                $subQuery
                    ->whereNull('orders.expires_at')
                    ->orWhere('orders.expires_at', '>', now());
            })
            ->sum('order_items.quantity');

        $this->setAttribute('reserved_quantity', (int) $reserved);

        return $this;
    }

    public function getReservedQuantityForAdmin(): int
    {
        if (array_key_exists('reserved_quantity', $this->attributes)) {
            return (int) $this->attributes['reserved_quantity'];
        }

        $this->loadReservedQuantity();

        return (int) $this->getAttribute('reserved_quantity');
    }

    public function getAvailableQuantityForAdmin(): int
    {
        return max(0, (int) $this->stock_quantity - $this->getReservedQuantityForAdmin());
    }
}
