<?php

namespace App\Repositories;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Models\ChartOfAccountCategory;
use App\Support\DataTables\DataTableQuery;
use App\Support\DataTables\EloquentDataTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChartOfAccountCategoryRepository implements ChartOfAccountCategoryRepositoryInterface
{
    public function paginate(DataTableQuery $dataTable): LengthAwarePaginator
    {
        return EloquentDataTable::paginate(
            ChartOfAccountCategory::query(),
            $dataTable,
            ['name'],
            [],
            [
                'id' => 'id',
                'name' => 'name',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'name',
            'asc'
        );
    }
}
