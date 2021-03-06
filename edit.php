<?php

require_once('config.php');
require_once('functions.php');

$errors = array();

// 受け取ったレコードのID
$id = $_GET['id'];

// データベースへの接続
$dbh = connectDb();

// SQLの準備と実行
$sql = "select * from plans where id = :id";
$stmt = $dbh->prepare($sql1);
$stmt->bindParam(":id", $id);
$stmt->execute();

// 結果の取得
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// タスクの編集
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 受け取ったデータ
  $title = $_POST['title'];
  $due_date = $_POST['due_date'];

  // エラーチェック用の配列

  // バリデーション
  if ($title == '') {
    $errors['title'] = 'タスク名が変更されていません';
  }

  if ($due_date == $post['due_date']) {
    $errors['due_date'] = '日付が変更されていません';
  }

  // エラーが1つもなければレコードを更新
  if (empty($errors)) {
    $dbh = connectDb();

    $sql = "update plans set title = :title, due_date = :due_date, updated_at = now() where id = :id";

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":due_date", $due_date);
    $stmt->bindParam(":id", $id);
    $stmt->execute();

    header('Location: index.php');
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>編集画面</title>
</head>

<body>
  <h2>編集</h2>
  <p>
    <form action="" method="post">
      <label for="title">学習内容:
        <input type="text" name="title" value="<?php echo h($post['title']); ?>">
      </label>
      <label for="due_date">期限日:
        <input type="date" name="due_date" value="due_date">
      </label>
      <input type="submit" value="編集">
        <?php if (count($errors) > 0) : ?>
          <ul style="color:red;">
            <?php foreach ($errors as $key => $value) : ?>
              <li><?php echo h($value); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
    </form>
  </p>
</body>

</html>