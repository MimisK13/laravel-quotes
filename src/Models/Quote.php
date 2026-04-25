<?php

namespace Mimisk\LaravelQuotes\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mimisk\LaravelQuotes\Enums\DiscountType;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;

/**
 * @property QuoteStatus $status
 * @property DiscountType $discount_type
 * @property float $discount_value
 * @property float $subtotal
 * @property float $tax_total
 * @property float $discount_total
 * @property float $total
 */
class Quote extends EloquentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_type',
        'owner_id',
        'number',
        'status',
        'title',
        'notes',
        'currency',
        'discount_type',
        'discount_value',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'owner_id' => 'integer',

            'status' => QuoteStatus::class,
            'discount_type' => DiscountType::class,

            'discount_value' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<EloquentModel, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<EloquentModel, $this>
     */
    public function items(): HasMany
    {
        $relatedModel = config('laravel-quotes.models.quote_item', QuoteItem::class);

        /** @var class-string<EloquentModel> $relatedModel */
        return $this->hasMany($relatedModel);
    }
}

