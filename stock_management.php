<?php
$type_list = ['未選択', '純米大吟醸', '純米吟醸', '特別純米', '純米酒', '大吟醸', '吟醸', '本醸造'];
$taste_list = ['未選択', '甘口', '中口', '辛口', 'スッキリ', 'コクあり'];
$category_list = ['日本酒', 'おつまみ'];

$error = [];
$message = '';
$host = 'localhost';
$user_name = 'codecamp48798';
$password = 'codecamp48798';
$dbname = 'codecamp48798';
$charset = 'utf8';

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

$img_dir    = './img/';

session_start();

if (isset($_SESSION['user_admin']) === false || $_SESSION['user_admin'] !== 1) {
    header('location:login.php');
    exit;
}

try {
    $dbh = new PDO($dsn, $user_name, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['append_item']) === true) {
            $goods_name = '';
            $goods_price = '';
            $goods_stock = '';
            $goods_type = '';
            $goods_taste = '';
            $goods_category = '';
            $new_img_filename = '';
            $status = '';
            $description = '';

            if (is_uploaded_file($_FILES['goods_img']['tmp_name']) === TRUE) {

                $extension = pathinfo($_FILES['goods_img']['name'], PATHINFO_EXTENSION);

                if ($extension === 'jpeg' || $extension === 'jpg' || $extension === 'png') {

                    $new_img_filename = sha1(uniqid(mt_rand(), true)) . '.' . $extension;

                    if (is_file($img_dir . $new_img_filename) !== TRUE) {

                        if (move_uploaded_file($_FILES['goods_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {
                            $error[] = 'ファイルアップロードに失敗しました';
                        }
                    } else {
                        $error[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                    }
                } else {
                    $error[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGのみ利用可能です。';
                }
            } else {
                $error[] = 'ファイルを選択してください';
            }

            if (isset($_POST['goods_name']) === true) {
                $goods_name = $_POST['goods_name'];
            }
            if (isset($_POST['goods_price']) === true) {
                $goods_price = $_POST['goods_price'];
            }
            if (isset($_POST['goods_stock']) === true) {
                $goods_stock = $_POST['goods_stock'];
            }
            if (isset($_POST['goods_type']) === true) {
                $goods_type = $_POST['goods_type'];
            }
            if (isset($_POST['goods_taste']) === true) {
                $goods_taste = $_POST['goods_taste'];
            }
            if (isset($_POST['goods_category']) === true) {
                $goods_category = $_POST['goods_category'];
            }
            if (isset($_POST['description']) === true) {
                $description = $_POST['description'];
            }
            if (isset($_POST['status']) === true) {
                $status = $_POST['status'];
            }

            if ($goods_name === '') {
                $error[] = '商品名が入力されていません';
            } else if (mb_strlen($goods_name) > 100) {
                $error[] = '商品名は100文字以内で入力してください';
            }

            if ($goods_price === '') {
                $error[] = '価格が入力されていません';
            } else if (preg_match('/^[0-9]+$/', $goods_price) !== 1) {
                $error[] = '価格は0円以上を入力して下さい';
            }

            if ($goods_stock === '') {
                $error[] = '個数が入力されていません';
            } else if (preg_match('/^[0-9]+$/', $goods_stock) !== 1) {
                $error[] = '個数は0個以上を入力して下さい';
            }

            if (preg_match('/^[0-7]$/', $goods_type) !== 1) {
                $error[] = '日本酒の種類を選択して下さい';
            }

            if ($goods_taste !== '0' && $goods_taste !== '1' && $goods_taste !== '2' && $goods_taste !== '3' && $goods_taste !== '4' && $goods_taste !== '5') {
                $error[] = '味わいを選択して下さい';
            }

            if ($goods_category !== '0' && $goods_category !== '1') {
                $error[] = '商品の種類を選択して下さい';
            }

            if (mb_strlen($description) > 1000) {
                $error[] = '商品詳細文は1000文字以内で入力してください';
            }
            if ($status !== '0' && $status !== '1') {
                $error[] = 'ステータス値が不正です';
            }

            if (count($error) === 0) {

                $sql = 'INSERT INTO items(name,price,img,status,stock,type,taste,category,description,create_datetime,update_datetime) VALUES(?,?,?,?,?,?,?,?,?,NOW(),NOW())';

                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $goods_name, PDO::PARAM_STR);
                $stmt->bindValue(2, $goods_price, PDO::PARAM_INT);
                $stmt->bindValue(3, $new_img_filename, PDO::PARAM_STR);
                $stmt->bindValue(4, $status, PDO::PARAM_INT);
                $stmt->bindValue(5, $goods_stock, PDO::PARAM_INT);
                $stmt->bindValue(6, $goods_type, PDO::PARAM_INT);
                $stmt->bindValue(7, $goods_taste, PDO::PARAM_INT);
                $stmt->bindValue(8, $goods_category, PDO::PARAM_INT);
                $stmt->bindValue(9, $description, PDO::PARAM_STR);
                $stmt->execute();
                $message = '商品の登録が完了しました';
            }
        } else if (isset($_POST['update_stock']) === true) {
            $id = '';
            $stock = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['stock']) === true) {
                $stock = $_POST['stock'];
            }
            if ($stock === '') {
                $error[] = '個数が入力されていません';
            } else if (preg_match('/^[0-9]+$/', $stock) !== 1) {
                $error[] = '個数は0個以上を入力して下さい';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET stock = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $stock, PDO::PARAM_INT);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '在庫数を変更しました';
            }
        } else if (isset($_POST['update_type']) === true) {
            $id = '';
            $type = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['type']) === true) {
                $type = $_POST['type'];
            }
            if (preg_match('/^[0-7]$/', $type) !== 1) {
                $error[] = '日本酒の種類を選択して下さい';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET type = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $type, PDO::PARAM_INT);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '日本酒の種類を変更しました';
            }
        } else if (isset($_POST['update_taste']) === true) {
            $id = '';
            $taste = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['taste']) === true) {
                $taste = $_POST['taste'];
            }
            if (preg_match('/^[0-5]$/', $taste) !== 1) {
                $error[] = '味わいを選択して下さい';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET taste = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $taste, PDO::PARAM_INT);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '味わいを変更しました';
            }
        } else if (isset($_POST['update_category']) === true) {
            $id = '';
            $category = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['category']) === true) {
                $category = $_POST['category'];
            }
            if (preg_match('/^[0-1]$/', $category) !== 1) {
                $error[] = '商品の種類を選択して下さい';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET category = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $category, PDO::PARAM_INT);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '商品の種類を変更しました';
            }
        } else if (isset($_POST['status_button']) === true) {
            $id = '';
            $status = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['status']) === true) {
                $status = $_POST['status'];
            }
            if (preg_match('/^[0-1]$/', $status) !== 1) {
                $error[] = 'ステータスが不正です';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET status = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $status, PDO::PARAM_INT);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = 'ステータスを変更しました';
            }
        } else if (isset($_POST['update_description']) === true) {
            $id = '';
            $update_description = '';
            if (isset($_POST['id']) === true) {
                $id = $_POST['id'];
            }
            if (isset($_POST['description']) === true) {
                $update_description = $_POST['description'];
            }
            if (mb_strlen($update_description) > 1000) {
                $error[] = '商品詳細文は1000文字以内で入力してください';
            }
            if (count($error) === 0) {
                $sql = 'UPDATE items SET description = ?,update_datetime = NOW() WHERE id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $update_description, PDO::PARAM_STR);
                $stmt->bindValue(2, $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '商品詳細文を変更しました';
            }
        }
    }
    $sql = 'SELECT * FROM items';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $stmt_contents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error[] = '接続できませんでした　理由：' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>自動販売機管理ツール</title>
    <link rel="stylesheet" href="/codeShop/css/stock_management.css">
</head>

<body>
    <?php foreach ($error as $error_contents) { ?> //エラーがあった場合の表示
        <p><?php print $error_contents; ?></p>
    <?php } ?>
    <?php if ($message !== '') { ?>
        <p><?php print $message; ?></p>
    <?php } ?>
    <header>
        <h1>商品管理ページ</h1>
    </header>
    <h2>新規商品追加</h2>
    <form method="post" enctype="multipart/form-data">
        <p><label>名前：<input type="text" name="goods_name"></label></p>
        <p><label>商品画像 : <input type="file" name="goods_img"></label></p>
        <p><label>値段：<input type="number" name="goods_price"></label></p>
        <p><label>個数：<input type="number" name="goods_stock"></label></p>
        <h3><label for="description">商品詳細</label></h3>
        <p><textarea id="description" name="description" cols="40" rows="4"></textarea></p>
        <p>
            <lavel>日本酒のジャンル：<select name="goods_type">
                    <?php foreach ($type_list as $index_type => $name_type) { ?>
                        <option value="<?php print $index_type; ?>"><?php print $name_type; ?></option>
                    <?php } ?>
                </select></lavel>
        </p>
        <label>味わい:<select name="goods_taste"><br>
                <?php foreach ($taste_list as $index_taste => $name_taste) { ?>
                    <option value="<?php print $index_taste; ?>"><?php print $name_taste; ?></option>
                <?php } ?>
            </select></label><br>
        <label>商品カテゴリー:<select name="goods_category"><br>
                <?php foreach ($category_list as $index_category => $name_category) { ?>
                    <option value="<?php print $index_category; ?>"><?php print $name_category; ?></option>
                <?php } ?>
            </select></label><br>
        <select name="status"><br>
            <option value="0">非公開</option>
            <option value="1">公開</option>
        </select><br>
        <input type="submit" name="append_item" value="商品を追加">
    </form>
    <hr>
    <h2>商品情報変更</h2>
    <table>
        <caption>商品一覧</caption>
        <tr>
            <th>商品名</th>
            <th>商品画像</th>
            <th>価格</th>
            <th>個数</th>
            <th>日本酒の種類</th>
            <th>味わい</th>
            <th>カテゴリー</th>
            <th>商品詳細</th>
            <th>公開ステータス</th>
        </tr>
        <?php foreach ($stmt_contents as $goods) { ?>
            <tr>
                <td><?php print htmlspecialchars($goods['name'], ENT_QUOTES) ?></td>
                <td><img src="<?php print $img_dir . $goods['img']; ?>"></td>
                <td><?php print htmlspecialchars($goods['price'], ENT_QUOTES) ?></td>
                <td>
                    <form method="POST">
                        <input type="text" name="stock" value="<?php print htmlspecialchars($goods['stock'], ENT_QUOTES) ?>">個
                        <input type="submit" name="update_stock" value="変更" />
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES) ?>">
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <select name="type">
                            <?php foreach ($type_list as $index_type => $name_type) { ?>
                                <option value="<?php print $index_type; ?>" <?php if ($index_type === (int)$goods['type']) print 'selected'; ?>><?php print $name_type; ?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES) ?>">
                        <input type="submit" name="update_type" value="変更">
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <select name="taste">
                            <?php foreach ($taste_list as $index_taste => $name_taste) { ?>
                                <option value="<?php print $index_taste; ?>" <?php if ($index_taste === (int)$goods['taste']) print 'selected'; ?>><?php print $name_taste; ?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES) ?>">
                        <input type="submit" name="update_taste" value="変更">
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <select name="category">
                            <?php foreach ($category_list as $index_category => $name_category) { ?>
                                <option value="<?php print $index_category; ?>" <?php if ($index_category === (int)$goods['category']) print 'selected'; ?>><?php print $name_category; ?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES) ?>">
                        <input type="submit" name="update_category" value="変更">
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <textarea name="description" cols="20" rows="10"><?php print htmlspecialchars($goods['description'], ENT_QUOTES) ?></textarea>
                        <input type="submit" name="update_description" value="変更">
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES) ?>">
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php print htmlspecialchars($goods['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ((int)htmlspecialchars($goods['status'], ENT_QUOTES, 'UTF-8') === 0) { ?>
                            <input type="submit" name="status_button" value="非公開=>公開">
                            <input type="hidden" name="status" value='1'>
                        <?php } else { ?>
                            <input type="submit" name="status_button" value="公開=>非公開">
                            <input type="hidden" name="status" value='0'>
                        <?php } ?>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
    <footer>
        <p>Copryright&copy;ty All Rights Reserved.</p>
    </footer>
</body>

</html>