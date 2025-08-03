<?php
// (PHP部分は変更ありません)
$host = 'localhost';
$dbname = 's2422021';
$user = 's2422021'; 
$password = 'mo8tILAq'; 
$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";
try {
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (!isset($_GET['id'])) { die("エラー：スポットIDが指定されていません。"); }
    $spot_id = $_GET['id'];
    $sql = "SELECT spot_name, description, ST_AsText(location) AS location_text FROM photospots WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id', $spot_id, PDO::PARAM_INT);
    $stmt->execute();
    $spot = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$spot) { die("エラー：指定されたスポットが見つかりません。"); }
    $location_text = $spot['location_text'];
    $coords = explode(' ', trim(substr($location_text, 6, -1)));
    $lng = $coords[0];
    $lat = $coords[1];
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 450px; } /* 地図の高さを指定 */
    </style>
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
        
        <div id="map"></div>
    </div>
    
    <script>
        // ▼▼▼ 地図を生成するためのJavaScript ▼▼▼

        // 1. PHPから緯度・経度を受け取る
        const lat = <?php echo json_encode($lat); ?>;
        const lng = <?php echo json_encode($lng); ?>;

        // 2. 地図を初期化
        //    L.map('map')は、id="map"のdiv要素に地図を描画するという意味
        //    setView()で、地図の中心を[緯度, 経度]に、ズームレベルを17に設定
        const map = L.map('map').setView([lat, lng], 17);

        // 3. 大学の地図サーバーから地図タイルを読み込む
        L.tileLayer('https://osm.gdl.jp/styles/osm-bright-ja/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 4. スポットの場所にマーカーを立てる
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<?php echo json_encode($spot['spot_name']); ?>'); // マーカーをクリックした時のポップアップ
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>