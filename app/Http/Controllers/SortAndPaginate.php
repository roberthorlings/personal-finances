<?php
namespace App\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
        $skip = $request->get("skip", 0);

        $collection = $sortBy ? $query->orderBy($sortBy, $sortOrder) : $query;

        return $this->getSkippablePaginator($collection, $per_page, $page, $columns, $skip);
    }

    protected function getSkippablePaginator($query, $perPage, $page, $columns = ['*'], $skip = 0) {
        $results = ($total = $query->toBase()->getCountForPagination())
            ? $query->skip(($page - 1) * $perPage + $skip)->take($perPage)->get($columns)
            : $query->getModel()->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

}
