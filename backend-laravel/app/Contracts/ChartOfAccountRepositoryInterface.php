<?php

namespace App\Contracts;

use Yajra\DataTables\EloquentDataTable;

interface ChartOfAccountRepositoryInterface
{
    public function dataTable(): EloquentDataTable;
}
