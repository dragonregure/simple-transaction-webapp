<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChartOfAccountCategoryResource;
use App\Support\DataTables\DataTableQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChartOfAccountCategoryController extends Controller
{
    public function __construct(private readonly ChartOfAccountCategoryRepositoryInterface $categories)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return ChartOfAccountCategoryResource::collection(
            $this->categories->paginate(DataTableQuery::fromRequest($request))
        );
    }
}
