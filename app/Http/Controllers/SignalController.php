<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\DailyHistory;
use Carbon\Carbon;
use DateTimeZone;
use App\Holiday;
use Illuminate\Support\Arr;

class SignalController extends Controller
{

    public function index()
    {
        return view('signal');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_volume()
    {
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

        //view表示用のコレクションを作る ソートしたいため
        $view_daily_histories_buf = collect([]);
        foreach($daily_histories_0 as $daily_history_0) {
            $daily_history_minus1 = $daily_histories_minus1->where('stock_id', $daily_history_0->stock_id)->first();
            $deltavolume = floatval($daily_history_0->volume) / floatval($daily_history_minus1->volume);

            $view_daily_history = collect([]);
            $view_daily_history->put('code', $daily_history_0->stock->code);
            $view_daily_history->put('name', $daily_history_0->stock->name);
            $view_daily_history->put('price', $daily_history_0->stock->price);
            $view_daily_history->put('deltavolume', $deltavolume);
            $view_daily_history->put('volume', $daily_history_0->volume);
            $view_daily_history->put('minus1volume', $daily_history_minus1->volume);

            $view_daily_histories_buf->push($view_daily_history);
        }
        //dd($view_daily_histories_buf);

        //⊿出来高（倍率）の降順でソート
        $view_daily_histories = $view_daily_histories_buf->sortByDesc('deltavolume');
        $view_daily_histories->values()->all();
        //dd($view_daily_histories);

        //$daily_histories_0 = DailyHistory::whereIn('id', $array_0)->orderByDesc('price')->get();
        //$daily_histories_minus1 = DailyHistory::whereIn('id', $array_minus1)->orderByDesc('price')->get();
        //dd($daily_histories_0, $daily_histories_minus1);

        return view('signal_volume', compact('view_daily_histories','baseday_str'));
    }


    public function index_akasanpei()
    {
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
                                $baseday_str => $daily_history_0_buf->price);
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
            //var_dump($bizdayidx);
            //n営業日前(-1日する)の算出^^^

            //全銘柄分ループするvvv
            for ($arrayidx=0; $arrayidx < count($akasan_array); $arrayidx++) { 
                $stock_id = $akasan_array[$arrayidx]['stock_id'];
                //var_dump($stock_id);
                $price = $akasan_array[$arrayidx][$date_str];
                //dd($stock_id);

                $daily_history_n_ago_buf = DailyHistory::where('updated_at', 'LIKE', "%$n_bizday_ago_str%")
                                                            ->where('stock_id', $stock_id)
                                                            ->first();
                //stock_idで検索し、対象銘柄がヒットしなかった場合、除外し次のループへ
                //(銘柄を取り込んだ直後にこのようなケースとなる）
                if ($daily_history_n_ago_buf == null) {
                    //var_dump($daily_history_minus1_buf);
                    continue;
                }
                //赤三兵かチェックする
                if ($daily_history_n_ago_buf->price < $price) {
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
            //dd($akasan_array_buf);
        }  //n営業日前(-1日する)の算出^^^
        //dd($akasan_array);

        //取得した配列を表示用に加工する
        $akasan_disp_array = array();
        $array_temp = array();
        for ($arrayidx=0; $arrayidx < count($akasan_array); $arrayidx++) {
            $stock_id = $akasan_array[$arrayidx]['stock_id'];
            $code = DailyHistory::where('stock_id', $stock_id)->first()->stock->code;
            $name = DailyHistory::where('stock_id', $stock_id)->first()->stock->name;
            $price_0 = $akasan_array[$arrayidx][$date_array[0]];
            $price_1 = $akasan_array[$arrayidx][$date_array[1]];
            $price_2 = $akasan_array[$arrayidx][$date_array[2]];
            $price_3 = $akasan_array[$arrayidx][$date_array[3]];
            $price_delta = floatval($price_0) - floatval($price_3);
            if ((floatval($price_3) > 0) && ($price_3 != null)) {
                $price_rate = round((floatval($price_0) / floatval($price_3) * 100), 2);
            } else {
                $price_rate = "---";
            }
            //$akasan_array[$arrayidx]['price_delta'] = $price_delta;

            $array_temp = array($stock_id, $code, $name, $price_delta, $price_rate, $price_0, $price_1, $price_2, $price_3);
            array_push($akasan_disp_array, $array_temp);
            unset($array_temp);
        }
        //dd($akasan_disp_array);
        return view('signal_akasanpei', compact('akasan_disp_array', 'date_array'));
    }

    public function index_kurosanpei()
    {
        //DailyHistoryテーブルの任意の銘柄を更新日の降順で取得
        //DailyHistoryテーブルに存在する日付の直近日で比較する
        $dates = DailyHistory::where('stock_id', "1")
                            ->orderBy('updated_at', 'desc')
                            ->get();
        //dd($dates[0]->updated_at);
        $now = $dates[0]->updated_at;
        $baseday_str = $now->toDateString();
        //dd($now, $one_bizday_ago);

        $daily_histories_0_buf = DailyHistory::where('updated_at', 'LIKE', "%$baseday_str%")->get();
        //dd($daily_histories_0_buf);
        //最初に全銘柄分のstock_idと日付を格納した配列を作成
        //基準日の全stock_idを配列に格納する
        //この配列から条件を満たさない銘柄を削除していく
        $array_0 = array();
        $array_temp = array();
        foreach ($daily_histories_0_buf as $daily_history_0_buf) {
            $array_temp = array('stock_id' => $daily_history_0_buf->stock_id,
                                $baseday_str => $daily_history_0_buf->price);
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
        //3営業日分の現在値をチェックする
        for ($bizdayidx=0; $bizdayidx < 3; $bizdayidx++) { 
            $n_bizday_ago = $dates[$bizdayidx + 1]->updated_at;
            $n_bizday_ago_str = $n_bizday_ago->toDateString();
            //var_dump($bizdayidx);
            //n営業日前(-1日する)の算出^^^

            //全銘柄分ループするvvv
            for ($arrayidx=0; $arrayidx < count($kurosan_array); $arrayidx++) { 
                $stock_id = $kurosan_array[$arrayidx]['stock_id'];
                //var_dump($stock_id);
                $price = $kurosan_array[$arrayidx][$date_str];
                //dd($stock_id);

                $daily_history_n_ago_buf = DailyHistory::where('updated_at', 'LIKE', "%$n_bizday_ago_str%")
                                                            ->where('stock_id', $stock_id)
                                                            ->first();
                //stock_idで検索し、対象銘柄がヒットしなかった場合、除外し次のループへ
                //(銘柄を取り込んだ直後にこのようなケースとなる）
                if ($daily_history_n_ago_buf == null) {
                    //var_dump($daily_history_minus1_buf);
                    continue;
                }
                //黒三兵かチェックする
                if ($daily_history_n_ago_buf->price > $price) {
                    //黒三兵は数が多く、タイムアウトになってしまう場合があるため、絞り込み条件を追加
                    //変化の割合が0.985%以上かチェック
                    if ((floatval($daily_history_n_ago_buf->price) > 0) && ($daily_history_n_ago_buf->price != null)) {
                        $result = (floatval($price) / floatval($daily_history_n_ago_buf->price));
                        if ($result <= floatval(0.985)) {
                            $kurosan_array[$arrayidx][$n_bizday_ago_str] = $daily_history_n_ago_buf->price;
                            array_push($kurosan_array_buf, $kurosan_array[$arrayidx]);
                            //dd($kurosan_array_buf);
                        }
                    }
                }
            }   //全銘柄分ループ^^^

            $kurosan_array = $kurosan_array_buf;
            $kurosan_array_buf = array();    //空にする
            $date_str = $n_bizday_ago_str;
            array_push($date_array, $date_str);
            $carbondate = $n_bizday_ago;
            //dd($kurosan_array_buf);
        }  //n営業日前(-1日する)の算出^^^
        //dd($kurosan_array);

        //取得した配列を表示用に加工する
        $kurosan_disp_array = array();
        $array_temp = array();
        for ($arrayidx=0; $arrayidx < count($kurosan_array); $arrayidx++) {
            $stock_id = $kurosan_array[$arrayidx]['stock_id'];
            $code = DailyHistory::where('stock_id', $stock_id)->first()->stock->code;
            $name = DailyHistory::where('stock_id', $stock_id)->first()->stock->name;
            $price_0 = $kurosan_array[$arrayidx][$date_array[0]];
            $price_1 = $kurosan_array[$arrayidx][$date_array[1]];
            $price_2 = $kurosan_array[$arrayidx][$date_array[2]];
            $price_3 = $kurosan_array[$arrayidx][$date_array[3]];
            //$price_4 = $kurosan_array[$arrayidx][$date_array[4]];
            $price_delta = floatval($price_0) - floatval($price_3);
            if ((floatval($price_3) > 0) && ($price_3 != null)){
                $price_rate = round((floatval($price_0) / floatval($price_3) * 100), 2);
            } else {
                $price_rate = "---";
            }
            //$kurosan_array[$arrayidx]['price_delta'] = $price_delta;

            $array_temp = array($stock_id, $code, $name, $price_delta, $price_rate, $price_0, $price_1, $price_2, $price_3);
            array_push($kurosan_disp_array, $array_temp);
            unset($array_temp);
        }
        //dd($kurosan_disp_array);
        return view('signal_kurosanpei', compact('kurosan_disp_array', 'date_array'));
    }

    public function index_debug()
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

        return view('signal');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
