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
    private const SELECT_OPTION_LIMIT = 20;

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
     * @return array{results: list<array{id: int, text: string}>, pagination: array{more: bool}}
     */
    public function selectOptions(string $term = '', int $page = 1, int $perPage = self::SELECT_OPTION_LIMIT): array
    {
        $query = ChartOfAccountCategory::query()
            ->select(['id', 'name'])
            ->orderBy('name');

        if ($term !== '') {
            $query->where('name', 'like', $term . '%');
        }

        $paginator = $query->paginate(
            min(max($perPage, 1), 50),
            ['id', 'name'],
            'page',
            max($page, 1)
        );

        return [
            'results' => $paginator->getCollection()
                ->map(static fn (ChartOfAccountCategory $category): array => [
                    'id' => (int) $category->id,
                    'text' => (string) $category->name,
                ])
                ->values()
                ->all(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ];
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
