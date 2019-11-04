<?php

namespace App\Model;

use App\Model\Builder\CategoryBuilder;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = ['parent_id', 'name', 'key'];

    public function transactions() {
        return $this->hasMany('App\Model\Transaction');
    }

    public function stats() {
        return $this->hasMany('App\Model\CategoryStats');
    }

    /**
     * Computes the sub total of the associated stats
     */
    public function getSubtotal() {
        return $this->stats->sum('amount');
    }

    /**
     * Computes the grand total of the associated stats, including any of the children
     */
    public function getGrandTotal() {
        return $this->stats->sum('grand_total');
    }

    public static function withStats(int $year = null, int $month = null) {
        return Category::with([
            'stats' => function($query) use ($year, $month) {
                if($year != null) {
                    $query->where('year', '=', $year);
                }

                if($month) {
                    $query->where('month', '=', $month);
                }
            }
        ]);
    }

}
