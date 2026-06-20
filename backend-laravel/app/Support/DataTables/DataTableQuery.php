<?php

namespace App\Support\DataTables;

use Illuminate\Http\Request;

final readonly class DataTableQuery
{
    /**
     * @param  array<string, string>  $filters
     */
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $search,
        public ?string $sort,
        public string $direction,
        public array $filters,
    ) {
    }

    /**
     * @param  array<int, string>  $allowedFilters
     */
    public static function fromRequest(Request $request, array $allowedFilters = []): self
    {
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(100, max(1, (int) $request->integer('per_page', 15)));
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $rawFilters = $request->query('filter', []);
        $filterBag = is_array($rawFilters) ? $rawFilters : [];

        $filters = [];
        foreach ($allowedFilters as $filter) {
            $value = $filterBag[$filter] ?? $request->query($filter);
            if (is_string($value) && trim($value) !== '' && $value !== 'all') {
                $filters[$filter] = trim($value);
            }
        }

        $search = $request->query('search');
        $sort = $request->query('sort');

        return new self(
            page: $page,
            perPage: $perPage,
            search: is_string($search) && trim($search) !== '' ? trim($search) : null,
            sort: is_string($sort) && trim($sort) !== '' ? trim($sort) : null,
            direction: $direction,
            filters: $filters,
        );
    }

    public function filter(string $key): ?string
    {
        return $this->filters[$key] ?? null;
    }
}
