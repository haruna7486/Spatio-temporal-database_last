<?php
// データベースへの接続設定 
$host = 'localhost';
$dbname = 's2422074';
$user = 's2422074'; 
$password = '6rORn2uT'; 
$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQLを実行して全件取得
    $sql = "SELECT id, spot_name, description FROM photospots ORDER BY id";
    $stmt = $dbh->query($sql);
    $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>フォトスポット一覧</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="spots_list.php">TDL PhotoSpots</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="spots_list.php">スポット一覧</a></li>
                    <li class="nav-item"><a class="nav-link" href="spot_register.html">新規登録</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>フォトスポット一覧</h1>
        <p>データベースに登録されている情報を表示</p>

        <p>登録件数: <?php echo count($spots); ?>件</p>

        <table class="table table-striped table-hover">
          <thead>
            <tr><th>ID</th><th>スポット名</th><th>説明</th></tr>
          </thead>
          <tbody>
            <?php foreach ($spots as $spot): ?>
            <tr>
              <td><?php echo htmlspecialchars($spot['id']); ?></td>
              <td><a href="spot_detail.php?id=<?php echo htmlspecialchars($spot['id']); ?>"><?php echo htmlspecialchars($spot['spot_name']); ?></a></td>
              <td><?php echo htmlspecialchars($spot['description']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>