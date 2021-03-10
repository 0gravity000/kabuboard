@extends('layouts.app')

@section('content')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">銘柄一覧</h1>
    <!--
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/meigara/import">銘柄一覧インポート</a>
        </button>
      </div>
      <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
        <span data-feather="calendar"></span>
        This week
      </button>
    </div>
    -->
  </div>
<!--
  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
-->
  <h2>{{ $stocks->count() }} 件</h2>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
    <thead>
    <tr>
        <th>コード</th>
        <th>市場</th>
        <th>銘柄名</th>
        <th>業種</th>
        <th>#</th>
    </tr>
    </thead>
    <tbody>
        @foreach($stocks as $stock)
        <tr>
        <td>{{ $stock->code }}</td>
        <td>{{ $stock->market->name }}</td>
        <td>{{ $stock->name }}</td>
        <td>{{ $stock->industry->name }}</td>
        <td>#</td>
        </tr>
        @endforeach
    </tbody>
    </table>
  </div>
</main>

@endsection