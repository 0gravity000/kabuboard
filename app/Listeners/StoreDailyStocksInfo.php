<?php

namespace App\Listeners;

use App\DailyHistory;
use App\Events\DailyStocksCheck;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Stock;
use Goutte\Client;
use Illuminate\Support\Facades\Log;

class StoreDailyStocksInfo
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
     * @param  DailyStocksCheck  $event
     * @return void
     */
    public function handle(DailyStocksCheck $event)
    {
        $stocks = Stock::all();
        //stocks分ループ
        foreach ($stocks as $stock) {
            $stockcode = $stock->code;
            $marketcode = $stock->market->code;

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
            /* エラーになるのでコメントアウト
            //#main > div.selectFinTitle.yjL
            $notexist = $crawler->filter('table.stocksTable tr')->each(function ($node) {  //戻り値は配列
                $notexist_temp = $node->text();
                return $notexist_temp;
            });
            if($notexist[0]==' 一致する銘柄は見つかりませんでした') {
                Log::info($html.":".$notexist[0]);
            }
             */
            
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

            //日足用データ
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

            //7:00-9:00はYahooサイトはメンテナンス状態で通常の値でなくなる(---)ためDB登録しないようにする
            if (!is_numeric(floatval(str_replace(',','',$price[0])))) {
                continue; //何もしないで関数を抜ける
            }
            //DB登録 stocksテーブル            
            $stock->price = floatval(str_replace(',','',$price[0]));
            $stock->rate = floatval($rate);
            $stock->pre_end_price = floatval(str_replace(',','',$pre_end_price));
            $stock->start_price = floatval(str_replace(',','',$start_price));
            $stock->end_price = $stock->price;
            $stock->highest_price = floatval(str_replace(',','',$highest_price));
            $stock->lowest_price = floatval(str_replace(',','',$lowest_price));
            $stock->volume = floatval(str_replace(',','',$volume));

            $stock->save();
            //DB登録 daily_histories テーブル
            $daily_history = new DailyHistory;
            $daily_history->stock_id = $stock->id;
            $daily_history->price = $stock->price;
            $daily_history->rate = $stock->rate;
            $daily_history->pre_end_price = $stock->pre_end_price;
            $daily_history->start_price = $stock->start_price;
            $daily_history->end_price = $stock->price;
            $daily_history->highest_price = $stock->highest_price;
            $daily_history->lowest_price = $stock->lowest_price;
            $daily_history->volume = $stock->volume;
            $daily_history->save();

        }   //stocks分ループ END

    }
}
