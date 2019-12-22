<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AccountStats extends Model
{
    protected $fillable = ['account_id', 'year', 'month', 'balance'];

    public function account() {
        return $this->belongsTo('App\Model\Account');
    }
}
