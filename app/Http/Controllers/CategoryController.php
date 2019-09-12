<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Category;
use App\Resources\Category as CategoryResource;
use App\Resources\CategoryTree as CategoryTreeResource;

class CategoryController extends Controller
{
    const DEFAULT_PER_PAGE = 25;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return CategoryResource::collection(Category::paginate($request->get("per_page", self::DEFAULT_PER_PAGE)));
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
     * @param  \Illuminate\Http\Request  $request
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
