@extends('layouts.app')

@section('content')

@include('layouts.sidebar')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">リアルタイム銘柄監視</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime_checking">監視画面へ</a>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime/create">銘柄追加</a>
        </button>
      </div>
    </div>
  </div>
<!--
  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
-->
  <h2>監視設定</h2>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
    <thead>
    <tr>
        <th>コード</th>
        <th>銘柄名</th>
        <th>上限値（円）</th>
        <th>下限値（円）</th>
        <th>⊿変化率（％）</th>
        <th>#</th>
        <th>#</th>
    </tr>
    </thead>
    <tbody>
        @foreach($realtime_settings as $realtime_setting)
        <tr>
        <td>{{ $realtime_setting->stock->code }}</td>
        <td>{{ $realtime_setting->stock->name }}</td>
        <td>{{ $realtime_setting->upperlimit }}</td>
        <td>{{ $realtime_setting->lowerlimit }}</td>
        <td>{{ $realtime_setting->changerate }}</td>
        <td><a href="/realtime/edit/{{$realtime_setting->id}}">編集</a></td>
        <td><a href="/realtime/destroy_setting/{{$realtime_setting->id}}">削除</a></td>
        </tr>
        @endforeach
    </tbody>
    </table>
  </div>
</main>

@endsection
