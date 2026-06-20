<?php

namespace App\Repositories;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountRepository implements ChartOfAccountRepositoryInterface
{
    public function dataTable(): EloquentDataTable
    {
        return DataTables::eloquent($this->tableQuery())
            ->only(['id', 'code', 'name', 'category', 'actions'])
            ->whitelist(['id', 'code', 'name', 'category.name'])
            ->editColumn(
                'category',
                fn (ChartOfAccount $account): string => $account->category?->name ?? '-'
            )
            ->addColumn(
                'actions',
                fn (ChartOfAccount $account): string => view(
                    'chart-of-accounts.partials.table-actions',
                    ['account' => $account]
                )->render()
            )
            ->rawColumns(['actions']);
    }

    /**
     * @return Builder<ChartOfAccount>
     */
    private function tableQuery(): Builder
    {
        return ChartOfAccount::query()
            ->with('category')
            ->select([
                'chart_of_accounts.id',
                'chart_of_accounts.code',
                'chart_of_accounts.category_id',
                'chart_of_accounts.name',
            ]);
    }
}
