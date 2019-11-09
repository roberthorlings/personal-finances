<?php

namespace App\Resources;

class CategoryStatsTree extends Category
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'category' => parent::toArray($request),
            'subtotal' => round($this->getSubtotal(), 2),
            'total' => round($this->getGrandTotal(), 2),
            'children' => CategoryStatsTree::collection($this->children)
        ];
    }
}
