<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\DailyHistory;
use Carbon\Carbon;
use DateTimeZone;
use App\Holiday;
use App\SignalAkasanpei;
use App\SignalKurosanpei;
use App\SignalVolume;
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
        //当日 15:00-23:59の間にDailyStocksCheckタスクスケジュールする
        //DailyHistoryテーブルの任意の銘柄を更新日の降順で取得
        //DailyHistoryテーブルに存在する日付の直近日で比較する
        $dates = DailyHistory::where('stock_id', "1")
                            ->orderBy('updated_at', 'desc')
                            ->get();
        //dd($dates[0]->updated_at);
        $now = $dates[0]->updated_at;
        $baseday_str = $now->toDateString();
        //該当基準日のデータを⊿出来高（倍率）の降順でソート
        $signalvolumes = SignalVolume::where('baseday', 'LIKE', "%$baseday_str%")
                    ->orderByDesc('deltavolume')->get();

        return view('signal_volume', compact('signalvolumes', 'baseday_str'));
    }

    public function index_akasanpei()
    {
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

        //該当基準日のデータを⊿変化率の降順でソート
        $signalakasanpeis = SignalAkasanpei::where('baseday', 'LIKE', "%$baseday_str%")
                    ->orderByDesc('deltarate')->get();

        return view('signal_akasanpei', compact('signalakasanpeis', 'baseday_str'));
    }

    public function index_kurosanpei()
    {
        //当日 15:00-23:59の間にDailyStocksCheckタスクスケジュールする
        //DailyHistoryテーブルの任意の銘柄を更新日の降順で取得
        //DailyHistoryテーブルに存在する日付の直近日で比較する
        $dates = DailyHistory::where('stock_id', "1")
                            ->orderBy('updated_at', 'desc')
                            ->get();
        //dd($dates[0]->updated_at);
        $now = $dates[0]->updated_at;
        $baseday_str = $now->toDateString();

        //該当基準日のデータを⊿変化率の昇順でソート
        $signalkurosanpeis = SignalKurosanpei::where('baseday', 'LIKE', "%$baseday_str%")
                    ->orderBy('deltarate')->get();

        return view('signal_kurosanpei', compact('signalkurosanpeis', 'baseday_str'));
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
