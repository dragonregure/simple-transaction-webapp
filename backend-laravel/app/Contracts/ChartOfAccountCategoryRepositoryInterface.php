<?php

namespace App\Contracts;

use App\Support\DataTables\DataTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChartOfAccountCategoryRepositoryInterface
{
    public function paginate(DataTableQuery $dataTable): LengthAwarePaginator;
}
