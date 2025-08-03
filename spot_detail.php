<?php
// データベースへの接続設定
$host = 'muds.gdl.jp';
$dbname = 'photospots';
$user = 's2422074'; 
$password = '6rORn2uT'; 

$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    // エラーモードを例外に設定
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // URLからスポットIDを取得
    // $_GET['id']でURLパラメータ（例：?id=1）から'id'の値を取得する
    if (!isset($_GET['id'])) {
        die("エラー：スポットIDが指定されていません。");
    }
    $spot_id = $_GET['id'];

    // プリペアドステートメントを使ってSQLインジェクションを防ぐ
    // PostGISのST_AsText関数を使って、位置情報をテキスト形式で取得
    $sql = "SELECT id, name, description, ST_AsText(location) AS location_text FROM photospots WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id', $spot_id, PDO::PARAM_INT);
    $stmt->execute();
    $spot = $stmt->fetch(PDO::FETCH_ASSOC);

    // スポットが見つからなかった場合の処理
    if (!$spot) {
        die("エラー：指定されたスポットが見つかりません。");
    }

    // デバッグの要！緯度と経度をパースする処理

    $location_text = $spot['location_text'];
    
    // PHPのstring関数を使って、"POINT("と")"を取り除く
    // trim()で余分なスペースを削除し、explode()でスペース区切りで配列に分割する
    $coords = explode(' ', trim(substr($location_text, 6, -1)));

    $lng = $coords[0]; // 経度
    $lat = $coords[1]; // 緯度

    // デバッグ用にこの2行を追加 
    echo "デバッグ情報:";
    var_dump($lat, $lng);
    //  ここまで 

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($spot['name']); ?> - スポット詳細</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .spot-info p {
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            border-radius: 6px;
        }
        .map-container {
            margin-top: 30px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: 0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($spot['name']); ?></h1>
        <div class="spot-info">
            <p><strong>説明:</strong> <?php echo nl2br(htmlspecialchars($spot['description'])); ?></p>
        </div>
        
        <?php if ($lat && $lng): ?>
        <div class="map-container">
            <!-- Google Maps Embed API を使って地図を表示-->
            <iframe
                width="100%"
                height="400"
                frameborder="0"
                style="border:0"
                src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q=<?php echo urlencode($lat . ',' . $lng); ?>"
                allowfullscreen>
            </iframe>
        </div>
        <?php else: ?>
        <p>地図情報を表示できませんでした。</p>
        <?php endif; ?>

        <a href="spots_list.php" class="back-link">スポット一覧に戻る</a>
    </div>
</body>
</html>
