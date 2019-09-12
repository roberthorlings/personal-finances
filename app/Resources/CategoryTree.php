<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTree extends Category
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $category = parent::toArray($request);
        $category["children"] = CategoryTree::collection($this->children);

        return $category;
    }
}
