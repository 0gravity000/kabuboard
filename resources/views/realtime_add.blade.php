@extends('layouts.app')

@section('content')

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">リアルタイム銘柄監視</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
      <div class="btn-group mr-2">
        <button type="button" class="btn btn-sm btn-outline-secondary">
          <a href="/realtime_setting">戻る</a>
        </button>
      </div>
    </div>
  </div>
<!--
  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
-->
  <h2>銘柄追加</h2>
  @if ($errors->any())
  <div class="alert alert-danger">
      <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
  @endif
  @if (session('status'))
  <div class="alert alert-danger">
      {{ session('status') }}
  </div>
@endif

  <form method="POST" action="/realtime/store">
    {{ csrf_field() }}   
    <div class="form-group">
      <label for="code">コード</label>
      <input type="code" class="form-control" name="code">
      <small class="form-text text-muted">4桁のコードを入力してください。例)1301 （株）極洋</small>
    </div>
    <div class="form-group">
      <label for="name">銘柄名</label>
      <input class="form-control" id="disabledInput" type="text" placeholder="Disabled input here..." disabled>
    </div>
    <button type="submit" class="btn btn-primary">登録</button>
  </form>
</main>

@endsection
