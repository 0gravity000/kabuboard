<!DOCTYPE html>
<html lang="ja">
    <head>
      <meta charset="UTF-8">
      <title>kabuboard</title>
    </head>
    <body>
      <h1>条件が成立しました</h1>

      <p>コード: {{ $code }}</p>

      <p>銘柄名: {{ $name }}</p>

      @if($matchtype_id == 1)
      <p>上限値:</p>
      <p>監視設定値：{{ $upperlimit }} ｜監視チェック条件成立値：{{ $checking_price }}</p>
      @elseif($matchtype_id == 2)
      <p>下限値:</p>
      <p>監視設定値：{{ $lowerlimit }} ｜監視チェック条件成立値：{{ $checking_price }}</p>
      @else
      <p>変化率:</p>
      <p>監視設定値：{{ $changerate }} ｜監視チェック条件成立値：{{ $checking_rate }}</p>
      @endif

    </body>
</html>