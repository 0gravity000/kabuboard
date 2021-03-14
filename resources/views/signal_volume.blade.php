@extends('layouts.app')

@section('content')

@include('layouts.sidebar')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">シグナル（日足）</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group mr-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
              <a href="/signal_volume">出来高急増</a>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary">
              <a href="/signal_akasanpei">赤三兵</a>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary">
              <a href="/signal_kurosanpei">黒三兵</a>
            </button>
          </div>
        </div>
      </div>
	  <!--
      <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
	  -->
      <h2>出来高急増</h2>
      <h3>
        基準日：{{ $baseday_str }}
      </h3>
      <h4>
        判定条件：出来高が1営業日前より10倍以上の銘柄
      </h4>
      <div class="table-responsive">
        <table class="table table-striped table-sm">
        <thead>
        <tr>
            <th>コード</th>
            <th>銘柄名</th>
            <th>現在値（円）</th>
            <th>⊿出来高（倍率）</th>
            <th>出来高：基準日</th>
            <th>出来高：前日</th>
            <th>#</th>
        </tr>
        </thead>
        <tbody>
          @foreach($signalvolumes as $signalvolume)
            <tr>
            <td>{{ $signalvolume->stock->code }}</td>
            <td>{{ $signalvolume->stock->name }}</td>
            <td>{{ $signalvolume->stock->price }}</td>
            <td>{{ $signalvolume->deltavolume }}</td>
            <td>{{ $signalvolume->stock->volume }}</td>
            <td>{{ $signalvolume->minus1volume }}</td>
            <td>#</td>
            </tr>
          @endforeach
        </tbody>
        </table>
      </div>
    </main>

@endsection
