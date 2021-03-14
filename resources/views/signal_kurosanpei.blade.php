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
      <h2>黒三兵</h2>
      <h3>
        基準日：{{ $baseday_str }}
      </h3>
      <h4>
        判定基準：3営業日連続で値下がりしている銘柄
      </h4>
      <div class="table-responsive">
        <table class="table table-striped table-sm">
        <thead>
        <tr>
            <th>コード</th>
            <th>銘柄名</th>
            <th>⊿：変化率（%）</th>
            <th>⊿：現在値（円）</th>
            <th>現在値（円）</th>
            <th>現在値：1営業日前</th>
            <th>現在値：2営業日前</th>
            <th>現在値：3営業日前</th>
            <th>#</th>
        </tr>
        </thead>
        <tbody>
          @foreach($signalkurosanpeis as $signalkurosanpei)
            <tr>
            <td>{{ $signalkurosanpei->stock->code }}</td>
            <td>{{ $signalkurosanpei->stock->name }}</td>
            <td>{{ $signalkurosanpei->deltarate }}</td>
            <td>{{ $signalkurosanpei->deltaprice }}</td>
            <td>{{ $signalkurosanpei->stock->price }}</td>
            <td>{{ $signalkurosanpei->minus1price }}</td>
            <td>{{ $signalkurosanpei->minus2price }}</td>
            <td>{{ $signalkurosanpei->minus3price }}</td>
            <td>#</td>
            </tr>
          @endforeach
        </tbody>
        </table>
      </div>
    </main>

@endsection