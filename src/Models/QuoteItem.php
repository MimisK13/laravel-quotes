<?php

namespace Mimisk\LaravelQuotes\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends EloquentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'quote_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'subtotal',
        'tax_total',
        'total',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quote_id' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<EloquentModel, $this>
     */
    public function quote(): BelongsTo
    {
        $relatedModel = config('laravel-quotes.models.quote', Quote::class);

        /** @var class-string<EloquentModel> $relatedModel */
        return $this->belongsTo($relatedModel);
    }
}

