@extends('layouts.app')

@section('content')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">リアルタイム銘柄監視</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime_setting">設定画面へ</a>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime_checking">監視画面へ</a>
        </button>
      </div>
    </div>
  </div>
<!--
  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
-->
  <h2>監視条件設定</h2>
  <form method="POST" action="/realtime/update_setting">
    {{ csrf_field() }}
    <div class="form-group">
      <label for="code">コード</label>
      <input class="form-control" name="code" id="disabledInput" type="text" placeholder={{ $realtime_setting->stock->code }} disabled>
    </div>
    <div class="form-group">
      <label for="name">銘柄名</label>
      <input class="form-control" name="name" id="disabledInput" type="text" placeholder={{ $realtime_setting->stock->name }} disabled>
    </div>
    <div class="form-group">
      <label for="price">現在値</label>
      <input class="form-control" name="price" id="disabledInput" type="text" placeholder={{ $realtime_setting->stock->price }} disabled>
    </div>
    <div class="form-group">
      <label for="rate">変化率</label>
      <input class="form-control" name="rate" id="disabledInput" type="text" placeholder={{ $realtime_setting->stock->rate }} disabled>
    </div>
    <div class="form-group">
      <label for="ismatched_upperlimit">条件成立フラグ 上限値</label>
      <input class="form-control" name="ismatched_upperlimit" id="disabledInput" type="text" placeholder={{ $realtime_setting->ismatched_upperlimit }} disabled>
    </div>
    <div class="form-group">
      <label for="ismatched_lowerlimit">条件成立フラグ 下限値</label>
      <input class="form-control" name="ismatched_lowerlimit" id="disabledInput" type="text" placeholder={{ $realtime_setting->ismatched_lowerlimit }} disabled>
    </div>
    <div class="form-group">
      <label for="ismatched_changerate">条件成立フラグ ⊿変化率</label>
      <input class="form-control" name="ismatched_changerate" id="disabledInput" type="text" placeholder={{ $realtime_setting->ismatched_changerate }} disabled>
    </div>
    <div class="form-group">
      <label for="upperlimit">上限値</label>
      <input type="text" class="form-control" name="upperlimit" value={{ $realtime_setting->upperlimit }}>
      <small class="form-text text-muted">上限値（円）を設定してください</small>
    </div>
    <div class="form-group">
      <label for="lowerlimit">下限値</label>
      <input type="text" class="form-control" name="lowerlimit" value={{ $realtime_setting->lowerlimit }}>
      <small class="form-text text-muted">下限値（円）を設定してください</small>
    </div>
    <div class="form-group">
      <label for="changerate">⊿変化率</label>
      <input type="text" class="form-control" name="changerate"  value={{ $realtime_setting->changerate }}>
      <small class="form-text text-muted">変化率（％）の差分を設定してください</small>
    </div>
    <input type="hidden" name="id" value="{{ $realtime_setting->id }}">
    <button type="submit" class="btn btn-primary">登録</button>
    <small class="form-text text-muted">登録を実行すると、成立条件はリセットされます</small>
  </form>
</main>

@endsection
