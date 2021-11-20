<?php
$error = [];
$message = '';
$host = 'localhost';
$user_name = 'codecamp48798';
$password = 'codecamp48798';
$dbname = 'codecamp48798';
$charset = 'utf8';

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

try {
    $dbh = new PDO($dsn, $user_name, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $user_name = "";
        $password = "";
        $check_password = "";

        if (isset($_POST['user_name']) === true) {
            $user_name = $_POST['user_name'];
        }
        if (isset($_POST['password']) === true) {
            $password = $_POST['password'];
        }
        if (isset($_POST['check_password']) === true) {
            $check_password = $_POST['check_password'];
        }

        if ($user_name === '') {
            $error[] = 'ユーザー名が入力されていません';
        } else if (preg_match('/^([a-zA-Z0-9]{6,20})$/', $user_name) !== 1) {
            $error[] = 'ユーザー名は6文字以上の半角英数字で入力して下さい';
        }
        if ($password === '') {
            $error[] = 'パスワードが入力されていません';
        } else if (preg_match('/^([a-zA-Z0-9]{6,})$/', $password) !== 1) {
            $error[] = 'パスワードは6文字以上で入力して下さい';
        }
        if ($password !== $check_password) {
            $error[] = 'パスワードが一致しません';
        }

        if (count($error) === 0) {
            $sql = 'SELECT * FROM users WHERE user_name = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetch() !== false) {
                $error[] = "このユーザー名は既に登録されています";
            } else {
                $sql = 'INSERT INTO users(user_name,password,create_datetime,update_datetime) VALUES(?,?,NOW(),NOW())';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
                $stmt->bindValue(2, $password, PDO::PARAM_STR);
                $stmt->execute();
                header('location:success.php');
                exit;
            }
        }
    }
} catch (PDOException $e) {
    $error[] = '接続できませんでした　理由：' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>新規登録画面</title>
    <link rel="stylesheet" href="/codeShop/css/sign_up.css">
</head>

<body>
    <header>
        <img src="logo.jpeg">
        <h1>新規登録</h1>
    </header>
    <?php foreach ($error as $error_message) { ?>
        <p><?php print $error_message; ?></p>
    <?php } ?>
    <form method="POST">
        <p><label>ユーザー名<input type="text" name="user_name"></label></p>
        <p><label>パスワード<input type="password" name="password"></label></p>
        <p><label>パスワード(確認用)<input type="password" name="check_password"></label></p>
        <input type="submit" name="sign_up_button" value="新規登録" class="sign_up_button">
    </form>
    <form action="login.php">
        <input type="submit" value=">>ログインページへ戻る">
    </form>
    <footer>
        <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
    </footer>
</body>

</html>