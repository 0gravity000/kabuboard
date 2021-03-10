@extends('layouts.app')

@section('content')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">リアルタイム銘柄監視</h1>
    @if (Auth::check())
    <!-- ユーザーはログインしている -->
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime_setting">設定画面へ</a>
        </button>
      </div>
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime/update_checking">更新</a>
        </button>
      </div>
      <!--
      <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
        <span data-feather="calendar"></span>
        This week
      </button>
      -->
    </div>
    @endif
  </div>

@if (Auth::check())
  <!-- ユーザーはログインしている -->
  <h2>リアルタイム監視</h2>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
    <thead>
    <tr>
        <th>コード</th>
        <th>銘柄名</th>
        <th>現在値（円）</th>
        <th>設定値：上限</th>
        <th>!</th>
        <th>設定値：下限</th>
        <th>!</th>
        <th>変化率（％）</th>
        <th>⊿変化率</th>
        <th>設定値：⊿変化率</th>
        <th>!</th>
    </tr>
    </thead>
    <tbody>
        @foreach($realtime_settings as $realtime_setting)
        <tr>
        <td>{{ $realtime_setting->stock->code }}</td>
        <td>{{ $realtime_setting->stock->name }}</td>
        <td>{{ $realtime_setting->realtime_checking->price }}</td>
        <td>{{ $realtime_setting->upperlimit }}</td>
          @if ($realtime_setting->ismatched_upperlimit)
          <td><a href="/realtime/edit/{{$realtime_setting->id}}">条件成立</a></td>
          @else
            <td>監視中</td>
          @endif
        <td>{{ $realtime_setting->lowerlimit }}</td>
          @if ($realtime_setting->ismatched_lowerlimit)
          <td><a href="/realtime/edit/{{$realtime_setting->id}}">条件成立</a></td>
          @else
            <td>監視中</td>
          @endif
        <td>{{ $realtime_setting->realtime_checking->rate }}</td>
        @php
          $deltarate = abs($realtime_setting->realtime_checking->rate - $realtime_setting->realtime_checking->pre_rate);
        @endphp
        <td>{{ $deltarate }}</td>
        <td>{{ $realtime_setting->changerate }}</td>
          @if ($realtime_setting->ismatched_changerate )
            <td><a href="/realtime/edit/{{$realtime_setting->id}}">条件成立</a></td>
          @else
            <td>監視中</td>
          @endif
        </tr>
        @endforeach
    </tbody>
    </table>
  </div>

@else
  <p>ログインしてください</p>
@endif
</main>

@endsection
