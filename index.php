<?php
// 設定ファイルと関数ファイルを読み込む
require_once('config.php');
require_once('functions.php');


$errors = array();

// DBに接続
$dbh = connectDb(); // 特にエラー表示がなければOK
// 未完了レコードの取得
$sql1 = "select * from plans where status = 'notyet' order by due_date asc";

$stmt = $dbh->prepare($sql1);
$stmt->execute();
$incomplete_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 完了レコードの取得
$sql2 = "select * from plans where status = 'done' order by due_date desc ";
$stmt = $dbh->prepare($sql2);
$stmt->execute();
$complete_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 新規タスク追加
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // フォームに入力されたデータの受け取り
  $title = $_POST['title'];
  $due_date = $_POST['due_date'];
  // エラーチェック用の配列
  // バリデーション
  if ($title == '') {
    $errors['title'] = 'タスク名を入力してください';
  }
  if ($due_date == '') {
    $errors['due_date'] = '期限を入力してください';
  }
  if (empty($errors)) {
    $dbh = connectDb();
    $sql = "insert into plans (title, due_date, created_at, updated_at) values (:title, :due_date, now(), now())";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":due_date", $due_date);
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
      <label for="title">期限日:
        <input type="text" name="title">
      </label>
      <br>
      <label for="due_date">期限日:
        <input type="date" name="due_date" value="due_date">
      </label>
      <input type="submit" value="追加">
      <!-- ★ここはerrosにdue_dateも入ってくるので、foreachで回す -->
      <?php if (count($errors) > 0) : ?>
        <ul style="color:red;">
          <?php foreach ($errors as $key => $value) : ?>
            <li><?php echo h($value); ?></li>
            <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </form>
  </p>
  <h2>未達成</h2>
  <ul>
    <?php foreach ($incomplete_plans as $plan) : ?>
      <?php if (date('Y-m-d') >= $plan['due_date']) : ?>
        <li class="expired">
        <?php else : ?>
        <li>
        <?php endif; ?>
        <!-- タスク完了のリンクを追記 -->
        <a href="done.php?id=<?php echo h($plan['id']); ?>">[完了]</a>
        <!-- 編集用のリンクを追記 -->
        <a href="edit.php?id=<?php echo h($plan['id']); ?>">[編集]</a>
        <?php echo h($plan['title'] . date('Y/m/d', strtotime($plan['due_date']))); ?>
        </li>
      <?php endforeach; ?>
  </ul>
  <hr>
  <h2>達成済み</h2>
  <ul>
    <?php foreach ($complete_plans as $plan) : ?>
      <li>
        <?php echo h($plan['title']); ?>
      </li>
    <?php endforeach; ?>
  </ul>
</body>