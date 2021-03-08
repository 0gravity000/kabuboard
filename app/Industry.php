<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $guarded = [];

    public function stocks()
    {
        return $this->hasMany('App\Stock');
    }    
}
