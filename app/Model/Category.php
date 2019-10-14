<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = ['parent_id', 'name', 'key'];

    public function transactions() {
        return $this->hasMany('App\Model\Transaction');
    }
}
