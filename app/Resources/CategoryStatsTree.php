<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

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
