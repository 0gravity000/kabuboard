<?php

//このクラスは不要

namespace App;

use Illuminate\Support\Facades\Log;
use App\RealtimeChecking;
use App\RealtimeSetting;
use App\Stock;
use Goutte\Client;
use DateTime;

class InvokeUpdateStocksInfo {

    public function __invoke() {

        $realtime_checkings = RealtimeChecking::all();
        //realtime_checkings分ループ
        foreach ($realtime_checkings as $realtime_checking) {
            $realtime_setting = RealtimeSetting::where('id', $realtime_checking->realtime_setting_id)->first();
            $stockcode = $realtime_setting->stock->code;
            $stock = Stock::where('id', $realtime_setting->stock->id)->first();
            $marketcode = $stock->market->code;
            Log::debug('stockcode：'.$stockcode.' marketcode：'.$marketcode);

            //スクレイピング
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

            $client = new Client();   //composer require fabpot/goutte しておくこと
            $crawler = $client->request('GET', $html);
            Log::debug($html);

            //日足用データ取得
            //終値
            $price = $crawler->filter('table.stocksTable tr')->each(function ($node) {
                $price_temp = $node->filter('td')->eq(1)->text();
                return $price_temp;
            });
            Log::debug($price);

            //比率　加工前データ +-xx（x.xx%）
            #stockinf > div.stocksDtl.clearFix > div.forAddPortfolio > table > tbody > tr > td.change > span.icoUpGreen.yjMSt
            $ratestring = $crawler->filter('table.stocksTable tr')->each(function ($node) {
                $temp = $node->filter('td.change span')->eq(1)->text(); //-xx（x｡xx%）
                //dd($g_temp);
                return $temp;
            });
            Log::debug($ratestring);
            //比率　加工後データ x.xx%
            $startpos = mb_strpos($ratestring[0], '（');
            $endpos = mb_strpos($ratestring[0], '%');
            $rate = mb_substr($ratestring[0], $startpos+1, ($endpos-$startpos)-1);
            Log::debug($rate);
      
            //前日終値
            //#detail > div.innerDate > div:nth-child(1) > dl > dd > strong
            $pre_end_price = $crawler->filter('#detail > div.innerDate > div:nth-child(1) > dl > dd > strong')->text();
            Log::debug($pre_end_price);
            //始値
                //#detail > div.innerDate > div:nth-child(2) > dl > dd > strong      
            $start_price = $crawler->filter('#detail > div.innerDate > div:nth-child(2) > dl > dd > strong')->text();
            Log::debug($start_price);
            //高値
            //#detail > div.innerDate > div:nth-child(3) > dl > dd > strong
            $highest_price = $crawler->filter('#detail > div.innerDate > div:nth-child(3) > dl > dd > strong')->text();
            Log::debug($highest_price);
            //安値
            $lowest_price = $crawler->filter('#detail > div.innerDate > div:nth-child(4) > dl > dd > strong')->text();
            Log::debug($lowest_price);
            //出来高
            //#detail > div.innerDate > div:nth-child(5) > dl > dd > strong
            $volume = $crawler->filter('#detail > div.innerDate > div:nth-child(5) > dl > dd > strong')->text();
            Log::debug($volume);
            //Log::debug($price.':'.$ratestring.':'.$pre_end_price.':'.$start_price.':'.$highest_price.':'.$lowest_price);

            //DB登録 stocksテーブル            
            $stock->price = floatval(str_replace(',','',$price[0]));
            $stock->rate = floatval($rate);
            $stock->save();
            //DB登録 realtime_checkingsテーブル            
            $realtime_checking->pre_price = $realtime_checking->price;
            $realtime_checking->pre_price_checkingat = $realtime_checking->price_checkingat;
            $realtime_checking->price = floatval(str_replace(',','',$price[0]));
            $now = new DateTime();
            $realtime_checking->price_checkingat = $now->format('Y-m-d H:i:s');
            $realtime_checking->pre_rate = $realtime_checking->rate;
            $realtime_checking->pre_rate_checkingat = $realtime_checking->rate_checkingat;
            $realtime_checking->rate = floatval($rate);
            $now = new DateTime();
            $realtime_checking->rate_checkingat = $now->format('Y-m-d H:i:s');
            $realtime_checking->save();

        }   //realtime_checkings分ループ END
    }
}
