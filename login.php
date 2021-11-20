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

        $login_user_name = "";
        $login_password = "";

        if (isset($_POST['login_user_name']) === true) {
            $login_user_name = $_POST['login_user_name'];
        }
        if (isset($_POST['login_password']) === true) {
            $login_password = $_POST['login_password'];
        }

        if ($login_user_name === '') {
            $error[] = 'ユーザー名が入力されていません';
        }
        if ($login_password === '') {
            $error[] = 'パスワードが入力されていません';
        }

        if (count($error) === 0) {

            $sql = 'SELECT * FROM users WHERE user_name = ? AND password = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $login_user_name, PDO::PARAM_STR);
            $stmt->bindValue(2, $login_password, PDO::PARAM_STR);
            $stmt->execute();
            $stmt_contents = $stmt->fetch();
            if ($stmt_contents === false) {
                $error[] = "ユーザー名またはパスワードが違います";
            } else {
                session_start();
                $_SESSION['user_id']  = $stmt_contents['id'];
                $_SESSION['user_name'] = $stmt_contents['user_name'];
                $_SESSION['user_admin'] = $stmt_contents['user_admin'];
                if ($_SESSION['user_admin'] === 1) {
                    header('location:stock_management.php');
                } else {
                    header('location:item_list_page.php');
                }
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
    <title>ログイン画面</title>
    <link rel="stylesheet" href="/codeShop/css/login.css">
</head>

<body>
    <header>
        <img src="logo.jpeg">
        <h1>ログイン</h1>
    </header>
    <?php foreach ($error as $error_message) { ?>
        <p><?php print $error_message; ?></p>
    <?php } ?>
    <form method="POST">
        <p><label>ユーザーID<input type="text" name="login_user_name"></label></p>
        <p><label>パスワード<input type="password" name="login_password"></label></p>
        <input type="submit" name="loguin_button" value="ログイン">
    </form>
    <p>IDをお持ちでない方は</p>
    <form action="sign_up.php">
        <input type="submit" value="新規登録">
    </form>
    <footer>
        <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
    </footer>
</body>

</html>