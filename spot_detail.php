<?php
// データベースへの接続設定

$host = 'muds.gdl.jp'; 
$dbname = 's2422021';
$user = 's2422021'; 
$password = 'mo8tILAq'; 



$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    // エラーモードを例外に設定
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL文の修正！データベースの列名に合わせて「spot_name」に変更したぜ！
    $sql = "SELECT id, spot_name, description FROM photospots ORDER BY id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フォトスポット一覧</title>
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
        p {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>フォトスポット一覧</h1>
        <p>データベースに登録されている情報を表示します。</p>

        <p>登録件数: <?php echo count($spots); ?>件</p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>スポット名</th>
                    <th>説明</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($spots as $spot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($spot['id']); ?></td>
                    <!-- 属性名を「name」から「spot_name」に修正したぜ！ -->
                    <td><a href="spot_detail.php?id=<?php echo htmlspecialchars($spot['id']); ?>"><?php echo htmlspecialchars($spot['spot_name']); ?></a></td>
                    <td><?php echo htmlspecialchars($spot['description']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
