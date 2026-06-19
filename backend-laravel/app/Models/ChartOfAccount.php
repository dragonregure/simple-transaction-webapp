<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'category_id', 'name'])]
class ChartOfAccount extends Model
{
    public $timestamps = false;

    /**
     * @return BelongsTo<ChartOfAccountCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccountCategory::class, 'category_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
