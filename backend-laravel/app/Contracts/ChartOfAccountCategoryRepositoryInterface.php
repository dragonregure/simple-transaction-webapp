<?php

namespace App\Contracts;

use Yajra\DataTables\EloquentDataTable;

interface ChartOfAccountCategoryRepositoryInterface
{
    public function dataTable(): EloquentDataTable;

    /**
     * @return array{results: list<array{id: int, text: string}>, pagination: array{more: bool}}
     */
    public function selectOptions(string $term = '', int $page = 1, int $perPage = 20): array;
}
