<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RealtimeSetting extends Model
{
    protected $guarded = [];

    public function stock()
    {
        return $this->belongsTo('App\Stock');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }    

    public function realtime_checking()
    {
        return $this->hasOne('App\RealtimeChecking');
    }
    
    public function matched_history()
    {
        return $this->hasOne('App\MatchedHistory');
    }    
   
}
