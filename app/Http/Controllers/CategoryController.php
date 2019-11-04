<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Statistics\CategoryStatsGenerator;
use App\Resources\Category as CategoryResource;
use App\Resources\CategoryStatsTree as CategoryStatsTreeResource;
use App\Resources\CategoryTree as CategoryTreeResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use SortAndPaginate;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return CategoryResource::collection(
            $this->getPaginatedAndSorted($request, Category::query())
        );
    }

    /**
     * Display a tree listing of all categories
     *
     * @return \Illuminate\Http\Response
     */
    public function tree()
    {
        return CategoryTreeResource::collection(Category::get()->toTree());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = Category::create($request->all());

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $category->update($request->all());
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }

    /**
     * Generates summary statistics for categories
     *
     * @return \Illuminate\Http\Response
     */
    public function generateStats()
    {
        $generator = new CategoryStatsGenerator();
        $stats = $generator->run();

        return response()->json(["numStats" => count($stats)], 201);
    }

    /**
     * Returns a tree structure with summary statistics for all categories
     *
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request)
    {
        return CategoryStatsTreeResource::collection(
            Category
                ::withStats($request->get('year', null), $request->get('month', null))
                ->get()
                ->toTree()
        );
    }
}
