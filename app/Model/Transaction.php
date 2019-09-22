<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['date', 'description', 'opposing_account_name', 'opposing_account_iban', 'amount', 'category_id', 'account_id'];

    public function category() {
        return $this->belongsTo('App\Model\Category');
    }

    public function account() {
        return $this->belongsTo('App\Model\Account');
    }

}
