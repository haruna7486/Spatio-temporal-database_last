<?php
// データベースへの接続設定 (はるなさん担当)
$host = 'localhost';
$dbname = 's2422074';
$user = 's2422074';
$password = '6rORn2uT'; 
$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // URLからスポットIDを取得
    if (!isset($_GET['id'])) { die("エラー：スポットIDが指定されていません。"); }
    $spot_id = $_GET['id'];

    // SQLを実行して特定のIDのデータを1件取得
    $sql = "SELECT spot_name, description, ST_AsText(location) AS location_text FROM photospots WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id', $spot_id, PDO::PARAM_INT);
    $stmt->execute();
    $spot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$spot) { die("エラー：指定されたスポットが見つかりません。"); }

    // 緯度と経度をパースする
    $location_text = $spot['location_text'];
    $coords = explode(' ', trim(substr($location_text, 6, -1)));
    $lng = $coords[0]; // 経度
    $lat = $coords[1]; // 緯度

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($spot['spot_name']); ?> - スポット詳細</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="spots_list.php">TDL PhotoSpots</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="spots_list.php">スポット一覧</a></li>
                    <li class="nav-item"><a class="nav-link" href="spot_register.html">新規登録</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($spot['spot_name']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($spot['description'])); ?></p>
        <hr>

        <h2>地図</h2>
        <div class="ratio ratio-16x9">
            <iframe
                src="http://googleusercontent.com/maps/google.com/18<?php echo 'AIzaSyB5OmE1w5HxwmDyhizviYmjKHF9fwmLSlk'; // ← 2. ここを書き換える ?>&q=<?php echo htmlspecialchars($lat); ?>,<?php echo htmlspecialchars($lng); ?>"
                title="Google Map"
                allowfullscreen>
            </iframe>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>