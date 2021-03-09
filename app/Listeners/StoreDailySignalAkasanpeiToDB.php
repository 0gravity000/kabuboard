<?php

namespace App\Listeners;

use App\Events\DailySignalAkasanpei;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\DailyHistory;
use App\SignalAkasanpei;
use Illuminate\Support\Facades\Log;

class StoreDailySignalAkasanpeiToDB
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
     * @param  DailySignalAkasanpei  $event
     * @return void
     */
    public function handle(DailySignalAkasanpei $event)
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

        //赤三兵判定処理
        $akasan_array = $array_0;   //配列をコピー
        $date_str = $baseday_str;
        $akasan_array_buf = array();    //配列を初期化
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
            for ($arrayidx=0; $arrayidx < count($akasan_array); $arrayidx++) { 
                $stock_id = $akasan_array[$arrayidx]['stock_id'];
                //dd($stock_id);
                $price = $akasan_array[$arrayidx][$date_str];
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
                //赤三兵かチェックする
                //var_dump('*'.$daily_history_n_ago_buf->price, '-'.$price);
                if ($daily_history_n_ago_buf->price < $price) {  //daily_historiesテーブルのpriceを取得
                    //赤三兵は数が多く、タイムアウトになってしまう場合があるため、絞り込み条件を追加
                    //変化の割合が1.5%以上かチェック
                    if ((floatval($daily_history_n_ago_buf->price) > 0) && ($daily_history_n_ago_buf->price != null)) {
                        $result = (floatval($price) / floatval($daily_history_n_ago_buf->price));
                        if ($result >= floatval(1.015)) {
                            $akasan_array[$arrayidx][$n_bizday_ago_str] = $daily_history_n_ago_buf->price;
                            array_push($akasan_array_buf, $akasan_array[$arrayidx]);
                            //dd($akasan_array_buf);
                            //var_dump($daily_history_n_ago_buf->stock_id);
                        }
                    }
                }
            }   //全銘柄分ループ^^^

            $akasan_array = $akasan_array_buf;
            $akasan_array_buf = array();    //空にする
            $date_str = $n_bizday_ago_str;
            array_push($date_array, $date_str);
            $carbondate = $n_bizday_ago;
        }  //n営業日前(-1日する)の算出^^^
        //dd($akasan_array);

        //signal_akasanpeisテーブルに格納
        for ($arrayidx=0; $arrayidx < count($akasan_array); $arrayidx++) {
            $signal_akasanpei = new SignalAkasanpei;
            $signal_akasanpei->stock_id = $akasan_array[$arrayidx]['stock_id'];
            $price_0 = $akasan_array[$arrayidx][$date_array[0]];
            $signal_akasanpei->minus1price = $akasan_array[$arrayidx][$date_array[1]];
            $signal_akasanpei->minus2price = $akasan_array[$arrayidx][$date_array[2]];
            $signal_akasanpei->minus3price = $akasan_array[$arrayidx][$date_array[3]];
            $signal_akasanpei->deltaprice = floatval($price_0) - floatval($signal_akasanpei->minus3price);
            //ゼロ割チェック
            if ((floatval($signal_akasanpei->minus3price) > 0) && ($signal_akasanpei->minus3price != null)) {
                $signal_akasanpei->deltarate = round((floatval($price_0) / floatval($signal_akasanpei->minus3price) * 100), 2);
            } else {
                $signal_akasanpei->deltarate = "---";   //float型に文字入る？
                Log::info('deltarate 0割発生：'.$signal_akasanpei);
            }
            $signal_akasanpei->baseday = $now; //date型、dateTime型ではない
            $signal_akasanpei->save();
        }
    }
}
