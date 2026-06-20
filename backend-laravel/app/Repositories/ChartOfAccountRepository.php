<?php

namespace App\Repositories;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountRepository implements ChartOfAccountRepositoryInterface
{
    private const SELECT_OPTION_LIMIT = 20;

    public function dataTable(): EloquentDataTable
    {
        return DataTables::eloquent($this->tableQuery())
            ->only(['id', 'code', 'name', 'account_type', 'category', 'actions'])
            ->whitelist(['id', 'code', 'name', 'account_type', 'category.name'])
            ->editColumn(
                'account_type',
                fn (ChartOfAccount $account): string => $account->accountTypeLabel()
            )
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
     * @return array{results: list<array{id: int, text: string}>, pagination: array{more: bool}}
     */
    public function selectOptions(string $term = '', int $page = 1, int $perPage = self::SELECT_OPTION_LIMIT): array
    {
        $query = ChartOfAccount::query()
            ->select(['id', 'code', 'name'])
            ->orderBy('code')
            ->orderBy('name');

        if ($term !== '') {
            $query->where(static function (Builder $query) use ($term): void {
                $query->where('code', 'like', $term . '%')
                    ->orWhere('name', 'like', $term . '%');
            });
        }

        $paginator = $query->paginate(
            min(max($perPage, 1), 50),
            ['id', 'code', 'name'],
            'page',
            max($page, 1)
        );

        return [
            'results' => $paginator->getCollection()
                ->map(static fn (ChartOfAccount $account): array => [
                    'id' => (int) $account->id,
                    'text' => sprintf('%s - %s', (string) $account->code, (string) $account->name),
                ])
                ->values()
                ->all(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ];
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
                'chart_of_accounts.account_type',
                'chart_of_accounts.name',
            ]);
    }
}
