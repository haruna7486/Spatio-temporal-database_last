<?php
// データベースへの接続設定
$host = 'localhost'; // サーバーのホスト名
$dbname = 's2422074';
$user = 's2422074'; 
$password = '6rORn2uT'; 

$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POSTされたデータがあるか確認
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("エラー：このページはフォームからアクセスしてください。");
    }

    // ▼▼▼ ここを修正 ▼▼▼
    // 'lat'と'lng'という別々の名前でデータを直接受け取る
    $spot_name = $_POST['spot_name'];
    $description = $_POST['description'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    // データが空、または数字でない場合はエラー
    if (empty($lat) || !is_numeric($lat) || empty($lng) || !is_numeric($lng)) {
        die("エラー：位置情報が正しくありません。地図をクリックして場所を指定してください。");
    }
    // ▲▲▲ ここまで修正 ▲▲▲


    // データベースへの挿入。SQLインジェクションを防ぐプリペアドステートメントを使う
    $sql = "INSERT INTO photospots (spot_name, description, location) VALUES (:spot_name, :description, ST_GeomFromText(:point, 4326))";
    $stmt = $dbh->prepare($sql);

    // PostGIS用に POINT(経度 緯度) の文字列を作成
    $point = "POINT(" . $lng . " " . $lat . ")";

    // 値をバインドして、安全にSQLを実行
    $stmt->bindParam(':spot_name', $spot_name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':point', $point, PDO::PARAM_STR);
    $stmt->execute();

    // 登録成功のメッセージを表示
    echo "<h1>登録完了！</h1>";
    echo "<p>新しいフォトスポット「" . htmlspecialchars($spot_name) . "」が登録されました！</p>";
    echo "<a href='spots_list.php'>スポット一覧に戻る</a>";

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}