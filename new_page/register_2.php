<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spot_name = $_POST['spot_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';
    $celebrity_info = $_POST['celebrity_info'] ?? '';

    if (empty($spot_name) || !is_numeric($lat) || !is_numeric($lng)) {
        die("入力エラー：スポット名と位置情報は必須です。");
    }

    $photo_filename = '';
    if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $photo_filename = uniqid() . '_' . basename($_FILES['photo_file']['name']);
        move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . $photo_filename);
    } else {
        die("登録エラー：写真のアップロードに失敗しました。");
    }

    $host = 'localhost';
    $dbname = 's2422074'; // あなたのユーザー名
    $user = 's2422074';
    $password = '6rORn2uT';
    $dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

    try {
        $dbh = new PDO($dsn);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $sql_spot = "INSERT INTO photospots (spot_name, description, location) VALUES (:spot_name, :description, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)) RETURNING id";
        $stmt_spot = $dbh->prepare($sql_spot);
        $stmt_spot->bindParam(':spot_name', $spot_name);
        $stmt_spot->bindParam(':description', $description);
        $stmt_spot->bindParam(':lng', $lng);
        $stmt_spot->bindParam(':lat', $lat);
        $stmt_spot->execute();
        $new_spot_id = $stmt_spot->fetchColumn();

        $sql_photo = "INSERT INTO photos (spot_id, filename, celebrity_info) VALUES (:spot_id, :filename, :celebrity_info)";
        $stmt_photo = $dbh->prepare($sql_photo);
        $stmt_photo->bindParam(':spot_id', $new_spot_id);
        $stmt_photo->bindParam(':filename', $photo_filename);
        $stmt_photo->bindParam(':celebrity_info', $celebrity_info);
        $stmt_photo->execute();

        $dbh->commit();

        echo "登録完了！<a href='spots_list_2.php'>一覧に戻る</a>";

    } catch (PDOException $e) {
        $dbh->rollBack();
        die("データベースエラー：" . $e->getMessage());
    }
}
?>