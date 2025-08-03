<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>登録処理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // フォームから送信されたデータを取得
            $spot_name = $_POST['spot_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $lat = $_POST['lat'] ?? '';
            $lng = $_POST['lng'] ?? '';
            // ▼▼▼ 追加：有名人情報の受け取り ▼▼▼
            $celebrity_info = $_POST['celebrity_info'] ?? '';

            // 必須項目が空でないかを確認
            if (empty($spot_name) || !is_numeric($lat) || !is_numeric($lng)) {
                die("<h1>入力エラー</h1><p>スポット名と位置情報は必須です。</p>");
            }

            // ▼▼▼ 追加：写真アップロード処理 ▼▼▼
            $photo_filename = '';
            if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                $photo_filename = uniqid() . '_' . basename($_FILES['photo_file']['name']);
                move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . $photo_filename);
            } else {
                die("<h1>登録エラー</h1><p>写真のアップロードに失敗しました。</p>");
            }
            // ▲▲▲ ここまで ▲▲▲

            // データベースに接続
            $host = 'localhost';
            $dbname = 's2422074'; // ユーザー名を再度確認してください
            $user = 's2422074'; 
            $password = 'あなたのパスワード'; // あなたのパスワード
            $dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$password";

            try {
                $dbh = new PDO($dsn);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // ▼▼▼ 変更：トランザクションを開始 ▼▼▼
                $dbh->beginTransaction();

                // 1. photospotsテーブルに「場所」の情報を登録
                $sql_spot = "INSERT INTO photospots (spot_name, description, location) VALUES (:spot_name, :description, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)) RETURNING id";
                $stmt_spot = $dbh->prepare($sql_spot);
                $stmt_spot->bindParam(':spot_name', $spot_name, PDO::PARAM_STR);
                $stmt_spot->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt_spot->bindParam(':lng', $lng, PDO::PARAM_STR);
                $stmt_spot->bindParam(':lat', $lat, PDO::PARAM_STR);
                $stmt_spot->execute();
                $new_spot_id = $stmt_spot->fetchColumn(); // 登録したスポットの新しいIDを取得

                // ▼▼▼ 追加：photosテーブルに「写真」の情報を登録 ▼▼▼
                $sql_photo = "INSERT INTO photos (spot_id, filename, celebrity_info) VALUES (:spot_id, :filename, :celebrity_info)";
                $stmt_photo = $dbh->prepare($sql_photo);
                $stmt_photo->bindParam(':spot_id', $new_spot_id, PDO::PARAM_INT);
                $stmt_photo->bindParam(':filename', $photo_filename, PDO::PARAM_STR);
                $stmt_photo->bindParam(':celebrity_info', $celebrity_info, PDO::PARAM_STR);
                $stmt_photo->execute();

                // ▼▼▼ 変更：全ての処理が成功したら、変更を確定 ▼▼▼
                $dbh->commit();

                echo "<h1>登録完了！</h1><p>新しいフォトスポットが登録されました！</p><a href='spots_list.php' class='btn btn-primary'>スポット一覧に戻る</a>";

            } catch (PDOException $e) {
                // ▼▼▼ 変更：エラーが発生したら、変更を全て取り消し ▼▼▼
                $dbh->rollBack();
                die("データベースエラー：" . $e->getMessage());
            }
        }
        ?>
    </div>
</body>
</html>