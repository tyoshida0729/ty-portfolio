<?php
$error = [];
$host = 'localhost';
$user_name = 'codecamp48798';
$password = 'codecamp48798';
$dbname = 'codecamp48798';
$charset = 'utf8';
$img_dir    = './img/';
$username = '';
$userid = '';
$description = '';


$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

session_start();
if (isset($_SESSION['user_name']) === false) {
    header('location:login.php');
    exit;
} else {
    $username = $_SESSION['user_name'];
}
if (isset($_SESSION['user_id']) === true) {
    $userid = $_SESSION['user_id'];
}

try {
    $dbh = new PDO($dsn, $user_name, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $item_id = '';
    if (isset($_GET['item_id']) === true) {
        $item_id = $_GET['item_id'];
    } else {
        $error[] = '商品が選択されていません';
    }

    if (count($error) === 0) {
        $sql =  'SELECT * FROM items WHERE id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $description = $stmt->fetch();
        if ($description === false) {
            $error[] = '商品情報の取得に失敗しました';
        }
    }

    if (isset($_POST['cart_addition']) === true) {
        $item = '';
        if (isset($_POST['id']) === true) {
            $item = $_POST['id'];
        }

        $sql =  'SELECT * FROM carts WHERE user_id = ? AND item_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->bindValue(2, $item, PDO::PARAM_INT);
        $stmt->execute();
        $cart = $stmt->fetch();

        if ($cart === false) {
            $sql = 'INSERT INTO carts (user_id,item_id,carts_in_item,create_datetime,update_datetime) VALUES(?,?,1,NOW(),NOW())';
        } else {
            $sql = 'UPDATE carts SET carts_in_item = carts_in_item+1,update_datetime = NOW() WHERE user_id = ? AND item_id = ?';
        }
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->bindValue(2, $item, PDO::PARAM_INT);
        $stmt->execute();
    }
} catch (PDOException $e) {
    $error[] = '接続できませんでした　理由：' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="/codeShop/css/goods_detail.css">
</head>

<body>
    <header>
        <a href="item_list_page.php"><img src="logo.jpeg"></a>
        <div class="header_block">
            <p>ようこそ<?php print $username ?>さん</p>
            <form action="logout.php">
                <input type="submit" value="ログアウト">
            </form>
        </div>
        <a href="carts.php"><img src="shopping_cart.png" class="cart_img"></a>
    </header>
    <h1>商品詳細</h1>
    <div class="detail">
        <div class="detail_pucture">
            <?php foreach ($error as $message) { ?>
                <p><?php print $message ?></p>
            <?php } ?>
            <?php if (count($error) === 0) { ?>
                <img src="<?php print $img_dir . $description['img']; ?>" class="item_picture">
        </div>
        <div class="detail_text">
            <p><?php print htmlspecialchars($description['name'], ENT_QUOTES) ?></p>
            <p><?php print htmlspecialchars($description['price'], ENT_QUOTES) ?>円(税込)</p>
            <p><?php print htmlspecialchars($description['description'], ENT_QUOTES) ?></p>
            <?php if ($description['stock'] > 0) { ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php print $description['id'] ?>">
                    <input type="submit" name="cart_addition" value="カートへ追加する">
                </form>
            <?php } else { ?>
                <p>売り切れ</p>
            <?php } ?>
        <?php } ?>
        </div>
    </div>
    <footer>
        <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
    </footer>
</body>

</html>