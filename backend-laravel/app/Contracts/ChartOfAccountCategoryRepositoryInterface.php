<?php

namespace App\Contracts;

use App\Models\ChartOfAccountCategory;
use Illuminate\Database\Eloquent\Builder;

interface ChartOfAccountCategoryRepositoryInterface
{
    /**
     * @return Builder<ChartOfAccountCategory>
     */
    public function tableQuery(): Builder;
}
