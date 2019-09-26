<?php
namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

const DEFAULT_PER_PAGE = 25;

trait SortAndPaginate
{
    /**
     * Returns a paginator with a list of items based on the parameters in the request.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getPaginatedAndSorted(Request $request, Builder $query, array $columns = ['*'], int $defaultPerPage = DEFAULT_PER_PAGE): \Illuminate\Pagination\LengthAwarePaginator
    {
        $page = $request->get("page", 1);
        $per_page = $request->get("per_page", $defaultPerPage);
        $sortBy = $request->get("sortBy");
        $sortOrder = $request->get("sortOrder", 'asc');

        $collection = $sortBy ? $query->orderBy($sortBy, $sortOrder) : $query;

        return $collection->paginate($per_page, $columns, null, $page);
    }

}
