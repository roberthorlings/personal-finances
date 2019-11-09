<?php

namespace App\Resources;

use Illuminate\Support\Collection;

class CategoryStats extends Category
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
            'stats' => $this->toStatsArray($this->stats)
        ];
    }

    private function toStatsArray(Collection $stats)
    {
        if($stats == null) {
            return [];
        }

        return $stats
            ->filter(function(\App\Model\CategoryStats $stat) {
                return round($stat->amount, 2) != 0;
            })
            ->values()
            ->map(function(\App\Model\CategoryStats $stat) {
                return [
                    'month' => $stat->month,
                    'year' => $stat->year,
                    'amount' => round($stat->amount, 2)
                ];
            });
    }


}
