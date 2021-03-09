<?php

namespace App\Listeners;

use App\Events\DailySignalVolume;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\DailyHistory;
use App\SignalVolume;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Log;

class StoreDailySignalVolumeToDB
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
     * @param  DailySignalVolume  $event
     * @return void
     */
    public function handle(DailySignalVolume $event)
    {
        //
        //当日 15:00-23:59の間にタスクスケジュールすること
        //DailyHistoryテーブルの任意の銘柄を更新日の降順で取得
        //DailyHistoryテーブルに存在する日付の直近日で比較する
        $dates = DailyHistory::where('stock_id', "1")
                            ->orderBy('updated_at', 'desc')
                            ->get();
        //dd($dates[0]->updated_at);

        $now = $dates[0]->updated_at;
        $one_bizday_ago = $dates[1]->updated_at;
        
        $baseday_str = $now->toDateString();
        $one_bizday_ago_str = $one_bizday_ago->toDateString();
        //dd($now, $one_bizday_ago);
        //dd($baseday_str, $one_bizday_ago_str);

        //
        $daily_histories_0_buf = DailyHistory::where('updated_at', 'LIKE', "%$baseday_str%")->get();
        //dd($daily_histories_0_buf);
        $array_0 = array();
        $array_minus1 = array();
        //出来高急増判定
        foreach ($daily_histories_0_buf as $daily_history_0_buf) {
            $daily_history_minus1_buf = DailyHistory::where('updated_at', 'LIKE', "%$one_bizday_ago_str%")
                                                ->where('stock_id', $daily_history_0_buf->stock_id)
                                                ->first();
            //stock_idで検索し、対象銘柄がヒットしなかった場合、除外し次のループへ
            //(銘柄を取り込んだ直後にこのようなケースとなる）
            if ($daily_history_minus1_buf == null) {
                //var_dump($daily_history_minus1_buf);
                continue;
            }
            //dd($daily_history_0_buf, $daily_history_minus1_buf);
            //基準日と1営業日前の両方の出来高が0のものは除外
            if (intval($daily_history_0_buf->volume) == 0 && intval($daily_history_minus1_buf->volume) == 0) {
                continue;
            }
            //1営業日前の出来高が1以下のものは除外
            if (intval($daily_history_minus1_buf->volume) <= 1) {
                continue;
            }
        //出来高が1営業日前より10倍増えているかチェック
            if (intval($daily_history_0_buf->volume) >= intval($daily_history_minus1_buf->volume)*10) {
                //dd($daily_history_0_buf, $daily_history_minus1_buf);
                //dd($daily_history_0_buf->volume, $daily_history_minus1_buf->volume);
                array_push($array_0, $daily_history_0_buf->id);
                array_push($array_minus1, $daily_history_minus1_buf->id);
            }
        }
        //
        //dd($array_0, $array_minus1);
        $daily_histories_0 = DailyHistory::whereIn('id', $array_0)->get();
        $daily_histories_minus1 = DailyHistory::whereIn('id', $array_minus1)->get();

        //signal_volumesテーブルに格納
        foreach($daily_histories_0 as $daily_history_0) {
            $signal_volume = new SignalVolume;
            $signal_volume->stock_id = $daily_history_0->stock_id;
            $daily_history_minus1 = $daily_histories_minus1->where('stock_id', $daily_history_0->stock_id)->first();
            $signal_volume->deltavolume = floatval($daily_history_0->volume) / floatval($daily_history_minus1->volume);
            $signal_volume->minus1volume = $daily_history_minus1->volume;
            $signal_volume->baseday = $now; //date型、dateTime型ではない
            $signal_volume->save();
            //Log::info('signal_volume');
        }
    }
}
