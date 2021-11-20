<?php
$error = [];
$message = '';
$host = 'localhost';
$user_name = 'codecamp48798';
$password = 'codecamp48798';
$dbname = 'codecamp48798';
$charset = 'utf8';
$img_dir    = './img/';
$username = '';
$userid = '';
$price_list = [];
$taste_list = [];
$type_list = [];
$category_list = [];
$keyword = '';

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

    $sql = 'SELECT * FROM items WHERE status = 1';
    $where = '';
    if (isset($_GET['price_category']) && is_array($_GET['price_category'])) {
        $price_list = $_GET['price_category'];
        $price_range = '';
        foreach ($_GET['price_category'] as $price) {
            switch ($price) {
                case '1':
                    $price_range .= ($price_range === '' ? '(' : ' OR ') . 'price <= 999';                  // (price <= 999
                    break;
                case '2':
                    $price_range .= ($price_range === '' ? '(' : ' OR ') . 'price BETWEEN 1000 AND 1999';   // (price <= 999 OR price BETWEEN 1000 AND 1999
                    break;
                case '3':
                    $price_range .= ($price_range === '' ? '(' : ' OR ') . 'price BETWEEN 2000 AND 2999';   // (price <= 999 OR price BETWEEN 1000 AND 1999 OR price BETWEEN 2000 AND 2999
                    break;
                case '4':
                    $price_range .= ($price_range === '' ? '(' : ' OR ') . 'price BETWEEN 3000 AND 5000';
                    break;
                case '5':
                    $price_range .= ($price_range === '' ? '(' : ' OR ') . 'price >= 5001';
                    break;
            }
        }
        if ($price_range !== '') {
            $where .= ' AND ' . $price_range . ')';                                                         // AND (price <= 999 OR price BETWEEN 1000 AND 1999 OR price BETWEEN 2000 AND 2999)
        }
    }
    if (isset($_GET['taste_category']) && is_array($_GET['taste_category'])) {
        $taste_list = $_GET['taste_category'];
        $taste_category = implode(',', $_GET['taste_category']);
        if ($taste_category !== '') {
            $where .= ' AND taste IN (' . $taste_category . ')';
        }
    }
    if (isset($_GET['type_category']) && is_array($_GET['type_category'])) {
        $type_list = $_GET['type_category'];
        $type_category = implode(',', $_GET['type_category']);
        if ($type_category !== '') {
            $where .= ' AND type IN (' . $type_category . ')';
        }
    }
    if (isset($_GET['category']) && is_array($_GET['category'])) {
        $category_list = $_GET['category'];
        $category = implode(',', $_GET['category']);
        if ($category !== '') {
            $where .= ' AND category IN (' . $category . ')';
        }
    }

    if (isset($_GET['search_word']) && $_GET['search_word'] !== '') {
        $keyword = $_GET['search_word'];
        $where .= " AND name LIKE '%{$_GET['search_word']}%'";
    }

    $sql .= $where;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $stmt_contents = $stmt->fetchAll();


    if (isset($_POST['cart_addition']) === true) {
        $item_id = '';
        if (isset($_POST['id']) === true) {
            $item_id = $_POST['id'];
        }

        $sql =  'SELECT * FROM carts WHERE user_id = ? AND item_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $cart = $stmt->fetch();

        if ($cart === false) {
            $sql = 'INSERT INTO carts (user_id,item_id,carts_in_item,create_datetime,update_datetime) VALUES(?,?,1,NOW(),NOW())';
        } else {
            $sql = 'UPDATE carts SET carts_in_item = carts_in_item+1,update_datetime = NOW() WHERE user_id = ? AND item_id = ?';
        }
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
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
    <title>商品一覧</title>
    <link rel="stylesheet" href="/codeShop/css/item_list_page.css">
</head>

<body>
    <header>
        <img src="logo.jpeg">
        <div class="header_block">
            <p>ようこそ<?php print $username ?>さん</p>
            <form action="logout.php">
                <input type="submit" value="ログアウト">
            </form>
        </div>
        <a href="carts.php"><img src="shopping_cart.png" class="cart_img"></a>
    </header>
    <?php foreach ($error as $message) { ?>
        <p><?php print $message ?></p>
    <?php } ?>
    <div class="search">
        <h3>絞り込み</h3>
        <form method="get">
            <p>価格</p>
            <ul class="price_category">
                <li><input type="checkbox" name="price_category[]" value="1" <?php print in_array('1', $price_list) ? 'checked' : '' ?>>0〜999円</li>
                <li><input type="checkbox" name="price_category[]" value="2" <?php print in_array('2', $price_list) ? 'checked' : '' ?>>1000〜1999円</li>
                <li><input type="checkbox" name="price_category[]" value="3" <?php print in_array('3', $price_list) ? 'checked' : '' ?>>2000〜2999円</li>
                <li><input type="checkbox" name="price_category[]" value="4" <?php print in_array('4', $price_list) ? 'checked' : '' ?>>3000〜5000円</li>
                <li><input type="checkbox" name="price_category[]" value="5" <?php print in_array('5', $price_list) ? 'checked' : '' ?>>5001円〜</li>
            </ul>
            <ul class="type_category">
                <li><input type="checkbox" name="type_category[]" value="1" <?php print in_array('1', $type_list) ? 'checked' : '' ?>>純米大吟醸</li>
                <li><input type="checkbox" name="type_category[]" value="2" <?php print in_array('2', $type_list) ? 'checked' : '' ?>>純米吟醸</li>
                <li><input type="checkbox" name="type_category[]" value="3" <?php print in_array('3', $type_list) ? 'checked' : '' ?>>特別純米</li>
                <li><input type="checkbox" name="type_category[]" value="4" <?php print in_array('4', $type_list) ? 'checked' : '' ?>>純米酒</li>
                <li><input type="checkbox" name="type_category[]" value="5" <?php print in_array('5', $type_list) ? 'checked' : '' ?>>大吟醸</li>
                <li><input type="checkbox" name="type_category[]" value="6" <?php print in_array('6', $type_list) ? 'checked' : '' ?>>吟醸</li>
                <li><input type="checkbox" name="type_category[]" value="7" <?php print in_array('7', $type_list) ? 'checked' : '' ?>>本醸造</li>
            </ul>
            <p>味わい</p>
            <ul class="taste_category">
                <li><input type="checkbox" name="taste_category[]" value="1" <?php print in_array('1', $taste_list) ? 'checked' : '' ?>>甘口</li>
                <li><input type="checkbox" name="taste_category[]" value="2" <?php print in_array('2', $taste_list) ? 'checked' : '' ?>>中口</li>
                <li><input type="checkbox" name="taste_category[]" value="3" <?php print in_array('3', $taste_list) ? 'checked' : '' ?>>辛口</li>
                <li><input type="checkbox" name="taste_category[]" value="4" <?php print in_array('4', $taste_list) ? 'checked' : '' ?>>スッキリ</li>
                <li><input type="checkbox" name="taste_category[]" value="5" <?php print in_array('5', $taste_list) ? 'checked' : '' ?>>コクあり</li>
            </ul>
            <p class="searchWord"><label>検索キーワード<input type="text" name="search_word" value="<?php print $keyword ?>"></label></p>
            <ul class="category">
                <li><input type="checkbox" name="category[]" value="0" <?php print in_array('0', $category_list) ? 'checked' : '' ?>>日本酒</li>
                <li><input type="checkbox" name="category[]" value="1" <?php print in_array('1', $category_list) ? 'checked' : '' ?>>おつまみ</li>
            </ul>
            <input type="submit" value="検索" class="search_botton">
        </form>
    </div>
    <h1>商品一覧</h1>
    <div class="item_list">
        <?php foreach ($stmt_contents as $goods_list) { ?>
            <div class="list_space">
                <p class="items"><?php print htmlspecialchars($goods_list['name'], ENT_QUOTES) ?></p>
                <a href="goods_detail.php?item_id=<?php print $goods_list['id'] ?>"><img src="<?php print $img_dir . $goods_list['img']; ?>" class="item_pictures"></a>
                <p class="items"><?php print htmlspecialchars($goods_list['price'], ENT_QUOTES) ?>円(税込)</p>
                <?php if ($goods_list['stock'] > 0) { ?>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php print $goods_list['id'] ?>">
                        <input type="submit" name="cart_addition" value="カートへ追加する" class="addition_cart">
                    </form>
                <?php } else { ?>
                    <p class="sold_out">売り切れ</p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <footer>
        <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
    </footer>
</body>

</html>