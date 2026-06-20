<?php

namespace App\Contracts;

use Yajra\DataTables\EloquentDataTable;

interface ChartOfAccountCategoryRepositoryInterface
{
    public function dataTable(): EloquentDataTable;
}
