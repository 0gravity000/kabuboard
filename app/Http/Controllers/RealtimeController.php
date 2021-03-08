<?php

namespace App\Http\Controllers;

use App\RealtimeSetting;
use Illuminate\Http\Request;

use App\Stock;
use App\RealtimeChecking;
use App\Events\MinitlyStocksCheck;
use DateTime;
use App\MatchedHistory;
use DateTimeZone;

class RealtimeController extends Controller
{

    public function index_checking()
    {
        $realtime_checkings = RealtimeChecking::all();
        return view('realtime_checking', compact('realtime_checkings'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_setting()
    {
        $realtime_settings = RealtimeSetting::all();
        return view('realtime_setting', compact('realtime_settings'));
    }

    public function index_history()
    {
        $matched_histories = MatchedHistory::orderBy('matchedat', 'desc')->get();
        return view('realtime_history', compact('matched_histories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $realtime_settings = RealtimeSetting::all();
        return view('realtime_add', compact('realtime_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd(request()->code);
        $stocks = Stock::where('code', request()->code);
        //dd($codes->first());
        //codesテーブルにないコードの場合
        if($stocks->first() == null) {
            return redirect('/realtime/create');
            //dd('$codes->first() == null');
        } 

        $realtime_settings = RealtimeSetting::where('stock_id', $stocks->first()->id);
        //dd($realtimeSettings);
        //realtime_ettingsテーブルにすでに登録がある場合
        if($realtime_settings->first() != null) {
            //dd('$realtimeSettings->first() != null');
            return redirect('/realtime/create');
        } 

        $realtime_setting = new RealtimeSetting;
        //$code = new Code;
        //dd($code);
        $realtime_setting->user_id = 1;
        $realtime_setting->stock_id = $stocks->first()->id;
        $realtime_setting->ismatched_upperlimit = false;
        $realtime_setting->ismatched_lowerlimit = false;
        $realtime_setting->ismatched_changerate = false;
        //dd($realtime_setting);
        $realtime_setting->save();

        $realtime_checking = new RealtimeChecking;
        $realtime_checking->realtime_setting_id = $realtime_setting->id;
        $realtime_checking->save();

        //$realtime_settings = RealtimeSetting::all();
        return redirect('/realtime_setting');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RealtimeSetting  $realtimeSetting
     * @return \Illuminate\Http\Response
     */
    public function show(RealtimeSetting $realtimeSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RealtimeSetting  $realtimeSetting
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $realtime_setting = RealtimeSetting::where('id', $id)->first();

        return view('realtime_edit', compact('realtime_setting'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RealtimeSetting  $realtimeSetting
     * @return \Illuminate\Http\Response
     */
    public function update_checking(Request $request, RealtimeSetting $realtimeSetting)
    {
        //リアルタイム株価、変化率をスクレイピング
        event(new MinitlyStocksCheck());

        return redirect('/realtime_checking');
    }

    public function update_setting()
    {
        //dd(request());
        $realtime_setting = RealtimeSetting::where('id', request()->id)->first();

        $realtime_setting->upperlimit = request()->upperlimit;
        $now = new DateTime();
        $now->setTimeZone( new DateTimeZone('Asia/Tokyo'));
        $realtime_setting->upperlimit_settingat = $now->format('Y-m-d H:i:s');
        $realtime_setting->lowerlimit = request()->lowerlimit;
        $now = new DateTime();
        $now->setTimeZone( new DateTimeZone('Asia/Tokyo'));
        $realtime_setting->lowerlimit_settingat = $now->format('Y-m-d H:i:s');
        $realtime_setting->changerate = request()->changerate;
        $now = new DateTime();
        $now->setTimeZone( new DateTimeZone('Asia/Tokyo'));
        $realtime_setting->changerate_settingat = $now->format('Y-m-d H:i:s');
        $realtime_setting->ismatched_upperlimit = false;
        $realtime_setting->ismatched_lowerlimit = false;
        $realtime_setting->ismatched_changerate = false;
        $realtime_setting->save();
        //dd($realtime_setting);

        return redirect('/realtime_setting');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RealtimeSetting  $realtimeSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy_setting($id)
    {
        $realtime_setting = RealtimeSetting::where('id', $id)->first();
        //dd($realtime_setting);
        $realtime_setting->delete();

        $realtime_checking = RealtimeChecking::where('realtime_setting_id', $id)->first();
        $realtime_checking->delete();

        return redirect('/realtime_setting');

        //$realtime_settings = RealtimeSetting::all();
        //return view('realtime', compact('realtime_settings'));
    }

    public function destroy_history($id)
    {
        if ($id == 0) { //全銘柄削除
            MatchedHistory::query()->delete();
            return redirect('/realtime_history');
        } else {
            $matched_history = MatchedHistory::where('id', $id)->first();
        }
        //dd($realtime_setting);
        $matched_history->delete();
        return redirect('/realtime_history');
    }

}
