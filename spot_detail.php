<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>スポット詳細</title>
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
        <?php /* ▼▼▼ ここから下が、はるなさん担当のバックエンド処理で動的に変わる部分 ▼▼▼ */ ?>
        
        <h1>ここにスポット名が入ります</h1>
        <p>ここにスポットの説明文が入ります。</p>
        <hr>

        <h2>地図</h2>
        <div class="ratio ratio-16x9">
            <iframe
                src=""  
                title="Google Map"
                allowfullscreen>
            </iframe>
        </div>

        <?php /* ▲▲▲ バックエンド処理ここまで ▲▲▲ */ ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>