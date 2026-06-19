<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class ChartOfAccountCategory extends Model
{
    /**
     * @return HasMany<ChartOfAccount, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}
