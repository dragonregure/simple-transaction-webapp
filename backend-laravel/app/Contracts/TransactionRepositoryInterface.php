<?php

namespace App\Contracts;

use Yajra\DataTables\EloquentDataTable;

interface TransactionRepositoryInterface
{
    public function dataTable(): EloquentDataTable;
}
