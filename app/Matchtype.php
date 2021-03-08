<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Matchtype extends Model
{
    protected $guarded = [];

    public function matched_histories()
    {
        return $this->hasMany('App\MatchedHistory');
    }
}
