<?php

namespace App\Listeners;

use App\Events\DailySignalKurosanpei;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\DailyHistory;
use App\SignalKurosanpei;
use Illuminate\Support\Facades\Log;

class StoreDailySignalKurosanpeiToDB
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
     * @param  DailySignalKurosanpei  $event
     * @return void
     */
    public function handle(DailySignalKurosanpei $event)
    {
        //
        //当日 15:00-23:59の間にDailyStocksCheckタスクスケジュールする
        //DailyHistoryテーブルの任意の銘柄を更新日の降順で取得
        //DailyHistoryテーブルに存在する日付の直近日で比較する
        $dates = DailyHistory::where('stock_id', "1")
                            ->orderBy('updated_at', 'desc')
                            ->get();
        //dd($dates[0]->updated_at);
        $now = $dates[0]->updated_at;
        //最初に全銘柄分のstock_idと日付を格納した配列を作成
        $baseday_str = $now->toDateString();
        //dd($now, $one_bizday_ago);
        $daily_histories_0_buf = DailyHistory::where('updated_at', 'LIKE', "%$baseday_str%")->get();

        //基準日の全stock_idを配列に格納する
        //この配列から条件を満たさない銘柄を削除していく
        //dd($daily_histories_0_buf);
        $array_0 = array();
        $array_temp = array();
        foreach ($daily_histories_0_buf as $daily_history_0_buf) {
            $array_temp = array('stock_id' => $daily_history_0_buf->stock_id,
                                $baseday_str => $daily_history_0_buf->stock->price); //当日のみStockのpriceと同じ。stocsテーブルのpriceを取得 daily_historiesテーブルのpriceではない
            array_push($array_0, $array_temp);
            unset($array_temp);
        }
        //dd($array_0);

        //黒三兵判定処理
        $kurosan_array = $array_0;   //配列をコピー
        $date_str = $baseday_str;
        $kurosan_array_buf = array();    //配列を初期化
        $carbondate = $now;
        $date_array = array();
        array_push($date_array, $baseday_str);
        //３営業日分の現在値をチェックする
        for ($bizdayidx=0; $bizdayidx < 3; $bizdayidx++) { 
            $n_bizday_ago = $dates[$bizdayidx + 1]->updated_at;
            $n_bizday_ago_str = $n_bizday_ago->toDateString();
            //dd($n_bizday_ago_str, $date_str);
            //n営業日前(-1日する)の算出^^^

            //全銘柄分ループするvvv
            for ($arrayidx=0; $arrayidx < count($kurosan_array); $arrayidx++) { 
                $stock_id = $kurosan_array[$arrayidx]['stock_id'];
                //dd($stock_id);
                $price = $kurosan_array[$arrayidx][$date_str];
                //dd($price);

                $daily_history_n_ago_buf = DailyHistory::where('updated_at', 'LIKE', "%$n_bizday_ago_str%")
                                                            ->where('stock_id', $stock_id)
                                                            ->first();
                //dd($daily_history_n_ago_buf);
                //stock_idで検索し、対象銘柄がヒットしなかった場合、除外し次のループへ
                //(銘柄を取り込んだ直後にこのようなケースとなる）
                if ($daily_history_n_ago_buf == null) {
                    //dd($daily_history_n_ago_buf);
                    continue;
                }
                //黒三兵かチェックする
                //var_dump('*'.$daily_history_n_ago_buf->price, '-'.$price);
                if ($daily_history_n_ago_buf->price > $price) {  //daily_historiesテーブルのpriceを取得
                    //黒三兵は数が多く、タイムアウトになってしまう場合があるため、絞り込み条件を追加
                    //変化の割合が0.985%以上かチェック
                    if ((floatval($daily_history_n_ago_buf->price) > 0) && ($daily_history_n_ago_buf->price != null)) {
                        $result = (floatval($price) / floatval($daily_history_n_ago_buf->price));
                        if ($result <= floatval(0.985)) {
                            $kurosan_array[$arrayidx][$n_bizday_ago_str] = $daily_history_n_ago_buf->price;
                            array_push($kurosan_array_buf, $kurosan_array[$arrayidx]);
                            //dd($kurosan_array_buf);
                            //var_dump($daily_history_n_ago_buf->stock_id);
                        }
                    }
                }
            }   //全銘柄分ループ^^^

            $kurosan_array = $kurosan_array_buf;
            $kurosan_array_buf = array();    //空にする
            $date_str = $n_bizday_ago_str;
            array_push($date_array, $date_str);
            $carbondate = $n_bizday_ago;
        }  //n営業日前(-1日する)の算出^^^
        //dd($kurosan_array);

        //signal_kurosanpeisテーブルに格納
        for ($arrayidx=0; $arrayidx < count($kurosan_array); $arrayidx++) {
            $signal_kurosanpei = new SignalKurosanpei;
            $signal_kurosanpei->stock_id = $kurosan_array[$arrayidx]['stock_id'];
            $price_0 = $kurosan_array[$arrayidx][$date_array[0]];
            $signal_kurosanpei->minus1price = $kurosan_array[$arrayidx][$date_array[1]];
            $signal_kurosanpei->minus2price = $kurosan_array[$arrayidx][$date_array[2]];
            $signal_kurosanpei->minus3price = $kurosan_array[$arrayidx][$date_array[3]];
            $signal_kurosanpei->deltaprice = floatval($price_0) - floatval($signal_kurosanpei->minus3price);
            //ゼロ割チェック
            if ((floatval($signal_kurosanpei->minus3price) > 0) && ($signal_kurosanpei->minus3price != null)) {
                $signal_kurosanpei->deltarate = round((floatval($price_0) / floatval($signal_kurosanpei->minus3price) * 100), 2);
            } else {
                $signal_kurosanpei->deltarate = "---";   //float型に文字入る？
                Log::info('deltarate 0割発生：'.$signal_kurosanpei);
            }
            $signal_kurosanpei->baseday = $now; //date型、dateTime型ではない
            $signal_kurosanpei->save();
        }
    }
}
