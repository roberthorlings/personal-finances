<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Account extends Model
{
    protected $fillable = ['name','iban'];

    public function transactions() {
        return $this->hasMany('App\Model\Transaction');
    }

    public function stats() {
        return $this->hasMany('App\Model\AccountStats');
    }

    public static function withStats(int $year, int $month) {
        return Account::with([
            'stats' => function(Relation $query) use ($year, $month) {
                // We want to include the balance for the given month
                // There may not be a value in the database for this month (e.g. when there are no
                // transactions in this month). In that case, we should return the last balance we
                // know
                $query->where('year', '<=', $year);
                $query->where('month', '<=', $month);

                // On take the latest value
                $query->orderByDesc('year');
                $query->orderByDesc('month');
            }
        ]);
    }

}
