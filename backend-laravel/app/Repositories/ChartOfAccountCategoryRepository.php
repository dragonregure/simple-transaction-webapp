<?php

namespace App\Repositories;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\Eloquent\Builder;

class ChartOfAccountCategoryRepository implements ChartOfAccountCategoryRepositoryInterface
{
    /**
     * @return Builder<ChartOfAccountCategory>
     */
    public function tableQuery(): Builder
    {
        return ChartOfAccountCategory::query()
            ->select(['id', 'name', 'created_at', 'updated_at']);
    }
}
