<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name','iban'];

    public function transactions() {
        return $this->hasMany('App\Model\Transaction');
    }
}
