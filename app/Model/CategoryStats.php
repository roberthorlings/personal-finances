<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CategoryStats extends Model
{
    protected $fillable = ['category_id', 'year', 'month', 'amount'];

    public function category() {
        return $this->belongsTo('App\Model\Category');
    }

    public function getGrandTotal() { return $this->grand_total; }
    public function getSubtotal() { return $this->amount; }
}
