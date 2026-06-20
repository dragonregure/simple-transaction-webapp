<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'category_id', 'account_type', 'name'])]
class ChartOfAccount extends Model
{
    public const ACCOUNT_TYPE_INCOME = 'income';
    public const ACCOUNT_TYPE_EXPENSE = 'expense';
    public const ACCOUNT_TYPES = [
        self::ACCOUNT_TYPE_INCOME,
        self::ACCOUNT_TYPE_EXPENSE,
    ];
    public const ACCOUNT_TYPE_LABELS = [
        self::ACCOUNT_TYPE_INCOME => 'Income',
        self::ACCOUNT_TYPE_EXPENSE => 'Expense',
    ];

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

    public function accountTypeLabel(): string
    {
        return self::ACCOUNT_TYPE_LABELS[$this->account_type] ?? ucfirst((string) $this->account_type);
    }

    /**
     * @return array{debit: int, credit: int}
     */
    public function transactionAmountsFor(int $amount): array
    {
        if ($this->account_type === self::ACCOUNT_TYPE_INCOME) {
            return [
                'debit' => 0,
                'credit' => $amount,
            ];
        }

        return [
            'debit' => $amount,
            'credit' => 0,
        ];
    }
}
