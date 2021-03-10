@extends('layouts.app')

@section('content')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">履歴</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime/destroy_allhistory">全履歴削除</a>
        </button>
      </div>
    </div>
  </div>
<!--
  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
-->
  <h2>履歴</h2>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
    <thead>
    <tr>
        <th>コード</th>
        <th>銘柄名</th>
        <th>成立条件</th>
        <th>詳細</th>
        <th>成立日時</th>
        <th>#</th>
    </tr>
    </thead>
    <tbody>
      @foreach($matched_histories as $matched_history)
        <tr>
          <td>{{ $matched_history->realtime_setting->stock->code }}</td>
          <td>{{ $matched_history->realtime_setting->stock->name }}</td>
          <td>{{ $matched_history->matchtype->detail }}</td>
          <td>{{ $matched_history->memo }}</td>
          <td>{{ $matched_history->matchedat }}</td>
          <td><a href="/realtime/destroy_history/{{$matched_history->id}}">削除</a></td>
        </tr>
      @endforeach
    </tbody>
    </table>
  </div>
</main>

@endsection
