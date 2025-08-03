<?php
// データベースへの接続設定
// ここは遥菜さんの情報に書き換えてね！
$host = 'muds.gdl.jp'; // サーバーのホスト名
$dbname = 'photospots';
$user = 's000001'; // 例:s000001など、遥菜さんの学籍番号に書き換えてね
$password = 'YOUR_DB_PASSWORD'; // ★ここにデータベースのパスワードを入れる★

// IPアドレスで接続を試す場合は、こちらの行のコメントを外して使ってみてね！
// $host = '119.245.135.221';

$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

try {
    $dbh = new PDO($dsn);
    // エラーモードを例外に設定
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POSTされたデータがあるか確認するぜ！
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("エラー：このページはフォームからアクセスしてください。");
    }

    // フォームからのデータを取得して、セキュリティ対策をするぜ！
    $spot_name = $_POST['spot_name'];
    $description = $_POST['description'];
    $location_text = $_POST['location']; // 緯度と経度がカンマ区切りで入ってるはず！
    // celebrity_flgは、photosテーブルにデフォルト値を入れるため、ここでは取得しない
    $celebrity_info = 'ここに有名人情報を入力できます'; // デフォルト値を設定

    // 緯度・経度の文字列をパースするぜ！
    // 例: "35.12345,139.12345"という文字列を配列に分解する
    $coords = explode(',', $location_text);
    if (count($coords) !== 2) {
        die("エラー：位置情報の形式が正しくありません。");
    }
    $lat = trim($coords[0]);
    $lng = trim($coords[1]);

    // データベースへの挿入！まずphotospotsテーブルにスポット情報を登録するぜ！
    $sql_spot = "INSERT INTO photospots (spot_name, description, location) VALUES (:spot_name, :description, ST_GeomFromText('POINT(' || :lng || ' ' || :lat || ')', 4326))";
    $stmt_spot = $dbh->prepare($sql_spot);

    // 値をバインドして、安全にSQLを実行するぜ！
    $stmt_spot->bindParam(':spot_name', $spot_name, PDO::PARAM_STR);
    $stmt_spot->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt_spot->bindParam(':lng', $lng, PDO::PARAM_STR);
    $stmt_spot->bindParam(':lat', $lat, PDO::PARAM_STR);
    $stmt_spot->execute();

    // 今登録したスポットのIDを取得するぜ！
    $spot_id = $dbh->lastInsertId();

    // ファイルアップロードの処理をするぜ！
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

        // photosテーブルに写真情報を挿入するぜ！
        // 列名を`filename`と`celebrity_info`に修正したぜ！
        $sql_photo = "INSERT INTO photos (spot_id, filename, celebrity_info) VALUES (:spot_id, :filename, :celebrity_info)";
        $stmt_photo = $dbh->prepare($sql_photo);
        $stmt_photo->bindParam(':spot_id', $spot_id, PDO::PARAM_INT);
        $stmt_photo->bindParam(':filename', $photo_filename, PDO::PARAM_STR);
        $stmt_photo->bindParam(':celebrity_info', $celebrity_info, PDO::PARAM_STR);
        $stmt_photo->execute();
    }

    // 登録成功のメッセージを表示するぜ！
    echo "<h1>登録完了！</h1>";
    echo "<p>新しいフォトスポット「" . htmlspecialchars($spot_name) . "」が登録されました！</p>";
    echo "<a href='spots_list.php'>スポット一覧に戻る</a>";

} catch (PDOException $e) {
    die("データベース接続エラー：" . $e->getMessage());
}
?>
