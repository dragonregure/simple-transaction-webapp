<?php

namespace App\Repositories;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Models\ChartOfAccountCategory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountCategoryRepository implements ChartOfAccountCategoryRepositoryInterface
{
    public function dataTable(): EloquentDataTable
    {
        return DataTables::eloquent($this->tableQuery())
            ->only(['id', 'name', 'created_at', 'updated_at', 'actions'])
            ->whitelist(['id', 'name', 'created_at', 'updated_at'])
            ->editColumn(
                'created_at',
                fn (ChartOfAccountCategory $category): string => $this->formatDateTime($category->created_at)
            )
            ->editColumn(
                'updated_at',
                fn (ChartOfAccountCategory $category): string => $this->formatDateTime($category->updated_at)
            )
            ->addColumn(
                'actions',
                fn (ChartOfAccountCategory $category): string => view(
                    'chart-of-account-categories.partials.table-actions',
                    ['category' => $category]
                )->render()
            )
            ->rawColumns(['actions']);
    }

    /**
     * @return Builder<ChartOfAccountCategory>
     */
    private function tableQuery(): Builder
    {
        return ChartOfAccountCategory::query()
            ->select(['id', 'name', 'created_at', 'updated_at']);
    }

    private function formatDateTime(mixed $value): string
    {
        if (! $value instanceof DateTimeInterface) {
            return '-';
        }

        return $value->format('d M Y, H:i');
    }
}
