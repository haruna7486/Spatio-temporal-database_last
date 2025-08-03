<?php
$spot_id = $_GET['id'] ?? 0;
if ($spot_id <= 0) {
    die("スポットIDが正しく指定されていません。");
}
$host = 'localhost';
$dbname = 's2422074';
$user = 's2422074';
$password = '6rORn2uT';
$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // スポット情報を取得
    $sql_spot = "SELECT id, spot_name, description, ST_AsText(location) AS location_text FROM photospots WHERE id = :id";
    $stmt_spot = $dbh->prepare($sql_spot);
    $stmt_spot->bindParam(':id', $spot_id);
    $stmt_spot->execute();
    $spot = $stmt_spot->fetch(PDO::FETCH_ASSOC);
    if (!$spot) {
        die("指定されたスポットが見つかりません。");
    }
    // このスポットに紐づく写真の一覧を取得
    $sql_photos = "SELECT id, filename, celebrity_info FROM photos WHERE spot_id = :spot_id ORDER BY id";
    $stmt_photos = $dbh->prepare($sql_photos);
    $stmt_photos->bindParam(':spot_id', $spot_id);
    $stmt_photos->execute();
    $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);
    // 緯度と経度をパース
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
    <title><?php echo htmlspecialchars($spot['spot_name']); ?> - 詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 450px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        </nav>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($spot['spot_name']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($spot['description'])); ?></p>
        <hr>
        <h2>地図</h2>
        <div id="map"></div>
        <hr>
        <h2>登録された写真</h2>
        <div class="row">
            <?php if (empty($photos)): ?>
                <p>まだ写真が登録されていません。</p>
            <?php else: ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" class="card-img-top" alt="フォトスポットの写真">
                            <div class="card-body">
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($photo['celebrity_info'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        const lat = <?php echo json_encode($lat); ?>;
        const lng = <?php echo json_encode($lng); ?>;
        const map = L.map('map').setView([lat, lng], 17);
        L.tileLayer('https://osm.gdl.jp/styles/osm-bright-ja/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup('<?php echo json_encode($spot['spot_name']); ?>');
    </script>
</body>
</html>