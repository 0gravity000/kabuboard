<?php

namespace App\Listeners;

use App\Events\MinitlyStocksCheck;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use App\RealtimeChecking;
use App\RealtimeSetting;
use App\Stock;
use Goutte\Client;
use DateTime;
use App\MatchedHistory;
use App\Matchtype;

class UpdateStocksInfo
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
     * @param  MinitlyStocksCheck  $event
     * @return void
     */
    public function handle(MinitlyStocksCheck $event)
    {
        $realtime_checkings = RealtimeChecking::all();
        //realtime_checkings分ループ
        foreach ($realtime_checkings as $realtime_checking) {
            $realtime_setting = RealtimeSetting::where('id', $realtime_checking->realtime_setting_id)->first();
            $stockcode = $realtime_setting->stock->code;
            $stock = Stock::where('id', $realtime_setting->stock->id)->first();
            $marketcode = $stock->market->code;
            //Log::debug('stockcode：'.$stockcode.' marketcode：'.$marketcode);

            $marketmark = '.T';
            if ($marketcode == 8 or $marketcode == 9) {
              $marketmark = '.S';
            }
            if ($marketcode == 10 or $marketcode == 11) {
              $marketmark = '.F';
            }
            if ($marketcode == 12 or $marketcode == 13 or $marketcode == 14) {
              $marketmark = '.N';
            }
            $html = 'http://stocks.finance.yahoo.co.jp/stocks/detail/?code='.$stockcode.$marketmark;

            //スクレイピング
            $client = new Client();   //composer require fabpot/goutte しておくこと
            $crawler = $client->request('GET', $html);
            //要検討 URLが存在しない場合はどうなる
            //Log::debug($html);

            //毎分用データ取得
            //終値
            $price = $crawler->filter('table.stocksTable tr')->each(function ($node) {  //戻り値は配列
                $price_temp = $node->filter('td')->eq(1)->text();
                return $price_temp;
            });
            //Log::debug($price);

            //比率　加工前データ +-xx（x.xx%）
            #stockinf > div.stocksDtl.clearFix > div.forAddPortfolio > table > tbody > tr > td.change > span.icoUpGreen.yjMSt
            $ratestring = $crawler->filter('table.stocksTable tr')->each(function ($node) { //戻り値は配列
                $temp = $node->filter('td.change span')->eq(1)->text(); //-xx（x｡xx%）
                //dd($g_temp);
                return $temp;
            });
            //Log::debug($ratestring);
            //比率　加工後データ x.xx%
            $startpos = mb_strpos($ratestring[0], '（');
            $endpos = mb_strpos($ratestring[0], '%');
            $rate = mb_substr($ratestring[0], $startpos+1, ($endpos-$startpos)-1);
            //Log::debug($rate);

            /* 日足用データ
            //前日終値
            //#detail > div.innerDate > div:nth-child(1) > dl > dd > strong
            $pre_end_price = $crawler->filter('#detail > div.innerDate > div:nth-child(1) > dl > dd > strong')->text();
            //Log::debug($pre_end_price);
            //始値
                //#detail > div.innerDate > div:nth-child(2) > dl > dd > strong      
            $start_price = $crawler->filter('#detail > div.innerDate > div:nth-child(2) > dl > dd > strong')->text();
            //Log::debug($start_price);
            //高値
            //#detail > div.innerDate > div:nth-child(3) > dl > dd > strong
            $highest_price = $crawler->filter('#detail > div.innerDate > div:nth-child(3) > dl > dd > strong')->text();
            //Log::debug($highest_price);
            //安値
            $lowest_price = $crawler->filter('#detail > div.innerDate > div:nth-child(4) > dl > dd > strong')->text();
            //Log::debug($lowest_price);
            //Log::debug($price.':'.$ratestring.':'.$pre_end_price.':'.$start_price.':'.$highest_price.':'.$lowest_price);
            //出来高
            //#detail > div.innerDate > div:nth-child(5) > dl > dd > strong
            $volume = $crawler->filter('#detail > div.innerDate > div:nth-child(5) > dl > dd > strong')->text();
            //Log::debug($volume);
            */

            //7:00-9:00はYahooサイトはメンテナンス状態で通常の値でなくなる(---)ためDB登録しないようにする
            if (!is_numeric(floatval(str_replace(',','',$price[0])))) {
                continue; //何もしないで関数を抜ける
            }
            //DB登録 stocksテーブル            
            $stock->price = floatval(str_replace(',','',$price[0]));
            $stock->rate = floatval($rate);
            $stock->save();
            //DB登録 realtime_checkingsテーブル            
            if ($realtime_checking->pre_price == null) {  //null //pre_priceが空の場合 最初の1回
                $realtime_checking->price = $stock->price;
                $now = new DateTime();
                $realtime_checking->price_checkingat = $now->format('Y-m-d H:i:s');
                $realtime_checking->pre_price = $realtime_checking->price;
                $realtime_checking->pre_price_checkingat = $realtime_checking->price_checkingat;
            } else {      //pre_priceに値が設定されている場合
                $realtime_checking->pre_price = $realtime_checking->price;
                $realtime_checking->pre_price_checkingat = $realtime_checking->price_checkingat;
                $realtime_checking->price = $stock->price;
                $now = new DateTime();
                $realtime_checking->price_checkingat = $now->format('Y-m-d H:i:s');
            }

            if ($realtime_checking->pre_rate == null) { //null //pre_rateが空の場合 最初の1回
                $realtime_checking->rate = $stock->rate;
                $now = new DateTime();
                $realtime_checking->rate_checkingat = $now->format('Y-m-d H:i:s');
                $realtime_checking->pre_rate = $realtime_checking->rate;
                $realtime_checking->pre_rate_checkingat = $realtime_checking->rate_checkingat;
            } else {//rateに値が設定されている場合
                $realtime_checking->pre_rate = $realtime_checking->rate;
                $realtime_checking->pre_rate_checkingat = $realtime_checking->rate_checkingat;
                $realtime_checking->rate = $stock->rate;
                $now = new DateTime();
                $realtime_checking->rate_checkingat = $now->format('Y-m-d H:i:s');
            }
            $realtime_checking->save();

        }   //realtime_checkings分ループ END

        //条件成立かチェック
        $realtime_checkings = RealtimeChecking::all();

        foreach ($realtime_checkings as $realtime_checking) {
            //上限値チェック
            //dd($realtime_checking->realtime_setting->upperlimit);
            if ($realtime_checking->realtime_setting->ismatched_upperlimit == false) {
                if ($realtime_checking->realtime_setting->upperlimit != null) {
                    if ($realtime_checking->price >= $realtime_checking->realtime_setting->upperlimit) {
                        $realtime_setting = RealtimeSetting::where('id', $realtime_checking->realtime_setting_id)->first();
                        $realtime_setting->ismatched_upperlimit = true;
                        $realtime_setting->save();

                        $matched_history = new MatchedHistory;
                        $matched_history->realtime_setting_id = $realtime_checking->realtime_setting->id;
                        $matched_history->matchtype_id = Matchtype::where('type', 1)->first()->id;
                        $matched_history->memo = $realtime_checking->price;
                        $matched_history->matchedat = $realtime_checking->price_checkingat;
                        $matched_history->save();
                    }
                }
            }
            //下限値チェック
            if ($realtime_checking->realtime_setting->ismatched_lowerlimit == false) {
                if ($realtime_checking->realtime_setting->lowerlimit) {
                    if ($realtime_checking->price <= $realtime_checking->realtime_setting->lowerlimit) {
                        $realtime_setting = RealtimeSetting::where('id', $realtime_checking->realtime_setting_id)->first();
                        $realtime_setting->ismatched_lowerlimit = true;
                        $realtime_setting->save();
                        
                        $matched_history = new MatchedHistory;
                        $matched_history->realtime_setting_id = $realtime_checking->realtime_setting->id;
                        $matched_history->matchtype_id = Matchtype::where('type', 2)->first()->id;
                        $matched_history->memo = $realtime_checking->price;
                        $matched_history->matchedat = $realtime_checking->price_checkingat;
                        $matched_history->save();
                    }
                }
            }
            //比率チェック
            if ($realtime_checking->realtime_setting->ismatched_changerate == false) {
                if ($realtime_checking->realtime_setting->changerate) {
                    if (abs($realtime_checking->rate - $realtime_checking->pre_rate) >= abs($realtime_checking->realtime_setting->changerate)) {
                        $realtime_setting = RealtimeSetting::where('id', $realtime_checking->realtime_setting_id)->first();
                        $realtime_setting->ismatched_changerate = true;
                        $realtime_setting->save();

                        $matched_history = new MatchedHistory;
                        $matched_history->realtime_setting_id = $realtime_checking->realtime_setting->id;
                        $matched_history->matchtype_id = Matchtype::where('type', 3)->first()->id;
                        $matched_history->memo = $realtime_checking->rate;
                        $matched_history->matchedat = $realtime_checking->rate_checkingat;
                        $matched_history->save();
                    }    
                }
            }
        }   //条件成立かチェック END

    }   //public function handle END
}
