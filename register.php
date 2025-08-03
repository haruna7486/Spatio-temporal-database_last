<?php
// データベースへの接続設定
$host = 'muds.gdl.jp'; // サーバーのホスト名
$dbname = 's2422021';
$user = 's2422021'; 
$password = 'mo8tILAq'; 

$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    // エラーモードを例外に設定
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POSTされたデータがあるか確認
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("エラー：このページはフォームからアクセスしてください。");
    }

    // フォームからのデータを取得して、セキュリティ対策
    $spot_name = $_POST['spot_name'];
    $description = $_POST['description'];
    $location_text = $_POST['location']; // 緯度と経度がカンマ区切りで入ってる
    $celebrity_flg = isset($_POST['celebrity_flg']) ? 1 : 0; // チェックボックスがチェックされていれば1、されていなければ0

    // 緯度・経度の文字列をパース
    // 例: "35.12345,139.12345"という文字列を配列に分解する
    $coords = explode(',', $location_text);
    if (count($coords) !== 2) {
        die("エラー：位置情報の形式が正しくありません。");
    }
    $lat = trim($coords[0]);
    $lng = trim($coords[1]);

    // ファイルアップロードの処理
    $photo_filename = '';
    if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $tmp_name = $_FILES['photo_file']['tmp_name'];
        $photo_filename = basename($_FILES['photo_file']['name']);
        $destination = $upload_dir . $photo_filename;
        move_uploaded_file($tmp_name, $destination);
    }

    // データベースへの挿入。SQLインジェクションを防ぐプリペアドステートメントを使う
    // PostGISのST_GeomFromText関数を使って、TEXTからGEOGRAPHY型に変換する
    $sql = "INSERT INTO photospots (spot_name, description, location, photo_filename, celebrity_flg) VALUES (:spot_name, :description, ST_GeomFromText('POINT(' || :lng || ' ' || :lat || ')', 4326), :photo_filename, :celebrity_flg)";
    $stmt = $dbh->prepare($sql);

    // 値をバインドして、安全にSQLを実行
    $stmt->bindParam(':spot_name', $spot_name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':lng', $lng, PDO::PARAM_STR);
    $stmt->bindParam(':lat', $lat, PDO::PARAM_STR);
    $stmt->bindParam(':photo_filename', $photo_filename, PDO::PARAM_STR);
    $stmt->bindParam(':celebrity_flg', $celebrity_flg, PDO::PARAM_INT);
    $stmt->execute();

    // 登録成功のメッセージを表示
    echo "<h1>登録完了！</h1>";
    echo "<p>新しいフォトスポット「" . htmlspecialchars($spot_name) . "」が登録されました！</p>";
    echo "<a href='spots_list.php'>スポット一覧に戻る</a>";

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>
