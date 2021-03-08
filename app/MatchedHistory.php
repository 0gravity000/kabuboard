<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchedHistory extends Model
{
    protected $guarded = [];

    public function realtime_setting()
    {
        return $this->belongsTo('App\RealtimeSetting');
    }    

    public function matchtype()
    {
        return $this->belongsTo('App\Matchtype');
    }

}
