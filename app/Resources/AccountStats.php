<?php

namespace App\Resources;

use Illuminate\Support\Collection;

class AccountStats extends Account
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
            'account' => parent::toArray($request),
            'stats' => $this->toStatsArray($this->stats)
        ];
    }

    private function toStatsArray(Collection $stats) {
        // There may be many stats returned. We should use only the latest one (which is returned first)
        if($stats->isEmpty()) {
            return null;
        }

        $first = $stats->first();
        return [
            'year' => $first->year,
            'month' => $first->month,
            'balance' => floatval($first->balance)
        ];
    }
}
