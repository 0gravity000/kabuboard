<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];

    public function market()
    {
        return $this->belongsTo('App\Market');
    }

    public function industry()
    {
        return $this->belongsTo('App\Industry');
    }    

    public function realtime_settings()
    {
        return $this->hasMany('App\RealtimeSettings');
    }    

    public function daily_histories()
    {
        return $this->hasMany('App\DailyHistory');
    }    
    public function signal_volumes()
    {
        return $this->hasMany('App\SignalVolume');
    }    
}
