<?php

namespace App\Listeners;

use App\Events\DailyDBStoreCheck;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\DailyHistory;
use Carbon\Carbon;
use DateTimeZone;

class DeleteExtraRecodes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DailyDBStoreCheck  $event
     * @return void
     */
    public function handle(DailyDBStoreCheck $event)
    {
        $daily_history = DailyHistory::orderBy('created_at', 'asc')->first();
        $createddate = $daily_history->created_at;

        //現在
        $now = Carbon::now(new DateTimeZone('Asia/Tokyo'));
        //今日の日付
        $today = Carbon::create($now->year, $now->month, $now->day, 00, 00, 01);
        //基準日の日付 10日分
        $branchday = $today;
        for ($i = 0; $i < 10; $i++) { 
            $branchday = $branchday->subDay();
            //var_dump($branchday);
        }
        //dd($branchday);

        $daily_historys = DailyHistory::where('created_at', '<', $branchday)->get();
        //dd($daily_historys);
        foreach ($daily_historys as $daily_history) {
            $daily_history->delete();
        }
    }
}
