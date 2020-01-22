<?php

// 設定ファイルと関数ファイルを読み込む
require_once('config.php');
require_once('functions.php');

// DBに接続
$dbh = connectDb(); // 特にエラー表示がなければOK

// レコードの取得
$sql = "select * from plans";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 新規タスク追加
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // フォームに入力されたデータの受け取り
  $title = $_POST['title'];
  $dua_date = $_POST['dua_date'];

  // エラーチェック用の配列
  $errors = array();

  // バリデーション
  if ($title == '') {
    $errors['title'] = 'タスク名を入力してください';
  }

  if (empty($errors)) {
    $dbh = connectDb();

    $sql = "insert into plans (title, dua_date, created_at, updated_at) values (:title, :dua_date, now(), now())";

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":dua_date", $dua_date);
    $stmt->execute();

    // index.phpに戻る
    header('Location: index.php');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>学習管理アプリ</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1>学習管理アプリ</h1>
  <p>
    <form action="" method="post">
      <input type="text" name="title">
      <br>
      <label for="dua_date">期限日:</label>
      <input type="date" name="dua_date" value="dua_date">
      <input type="submit" value="追加">
      <span style="color:red;"><?php echo h($errors['title']) ?></span>
    </form>
  </p>

  <h2>未達成</h2>
  <ul>
    <?php
    $sql2 = "select * from plans order by dua_date asc";
    $stmt = $dbh->prepare($sql2);
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <?php foreach ($plans as $plan) : ?>
      <?php if ($plan['status'] == 'notyet') : ?>
        <?php if (date('Y-m-d') >= $plan['dua_date']) : ?>
          <li class="expired">
            <!-- タスク完了のリンクを追記 -->
            <a href="done.php?id=<?php echo h($plan['id']); ?>">[完了]</a>
            <!-- 編集用のリンクを追記 -->
            <a href="edit.php?id=<?php echo h($plan['id']); ?>">[編集]</a>
            <?php echo h($plan['title'] . $plan['dua_date']); ?>
          </li>
        <?php  else : ?>
          <li>
            <!-- タスク完了のリンクを追記 -->
            <a href="done.php?id=<?php echo h($plan['id']); ?>">[完了]</a>
            <!-- 編集用のリンクを追記 -->
            <a href="edit.php?id=<?php echo h($plan['id']); ?>">[編集]</a>
            <?php echo h($plan['title']. '  '. $plan['dua_date']); ?>
          </li>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>

  <hr>

  <h2>達成済み</h2>
  <ul>
    <?php foreach ($plans as $plan) : ?>
      <?php if ($plan['status'] == 'done') : ?>
        <li>
          <?php echo h($plan['title']); ?>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>

</body>

</html>