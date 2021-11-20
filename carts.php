 <?php
    $error = [];
    $message = '';
    $host = 'localhost';
    $user_name = 'codecamp48798';
    $password = 'codecamp48798';
    $dbname = 'codecamp48798';
    $charset = 'utf8';
    $username = '';
    $userid = '';


    $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

    $img_dir    = './img/';

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

        if (isset($_POST['change']) === true) {
            $item_id = '';
            $amount = '';
            if (isset($_POST['item_id']) === true) {
                $item_id = $_POST['item_id'];
            }
            if (isset($_POST['amount']) === true) {
                $amount = $_POST['amount'];
            }
            if ($amount === '') {
                $error[] = '数量が入力されていません';
            } else if (preg_match('/^[1-9][0-9]*$/', $amount) !== 1) {
                $error[] = '半角数字を入力して下さい';
            }

            if (count($error) === 0) {
                $sql = 'UPDATE carts SET carts_in_item = ?,update_datetime = NOW() WHERE user_id = ? AND item_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $amount, PDO::PARAM_INT);
                $stmt->bindValue(2, $userid, PDO::PARAM_INT);
                $stmt->bindValue(3, $item_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = '在庫数を変更しました';
            }
        } else if (isset($_POST['delete']) === true) {
            $item_id = '';
            if (isset($_POST['item_id']) === true) {
                $item_id = $_POST['item_id'];
            }

            $sql = 'DELETE FROM carts WHERE user_id = ? AND item_id = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $userid, PDO::PARAM_INT);
            $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
            $stmt->execute();
            $message = '商品を削除しました';
        }

        $sql = 'SELECT * FROM carts INNER JOIN items ON carts.item_id = items.id WHERE user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $stmt_contents = $stmt->fetchAll();

        $sum = 0;
        foreach ($stmt_contents as $item) {
            $sum += $item['carts_in_item'] * $item['price'];
        }
    } catch (PDOException $e) {
        $error[] = '接続できませんでした　理由：' . $e->getMessage();
    }
    ?>

 <!DOCTYPE html>
 <html lang="ja">

 <head>
     <meta charset="UTF-8">
     <title>カート</title>
     <link rel="stylesheet" href="/codeShop/css/carts.css">
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
     </header>
     <?php foreach ($error as $message) { ?>
         <p><?php print $message ?></p>
     <?php } ?>
     <h1>カート</h1>
     <?php if (empty($stmt_contents) === false) { ?>
         <table>
             <caption>カートに入っている商品</caption>
             <tr>
                 <th>商品名</th>
                 <th>商品画像</th>
                 <th>値段(税込)</th>
                 <th>個数</th>
                 <th>小計</th>
                 <th></th>
                 　　
             </tr>
             <?php foreach ($stmt_contents as $carts) { ?>
                 <tr>
                     <td><?php print htmlspecialchars($carts['name'], ENT_QUOTES) ?></td>
                     <td><img src="<?php print $img_dir . $carts['img']; ?>" class="cart_in_img"></td>
                     <td><?php print htmlspecialchars($carts['price'], ENT_QUOTES) ?></td>
                     <td>
                         <form method="post">
                             <input type='text' name="amount" value="<?php print htmlspecialchars($carts['carts_in_item'], ENT_QUOTES) ?>">
                             <input type='hidden' name="item_id" value="<?php print htmlspecialchars($carts['item_id'], ENT_QUOTES) ?>">
                             <input type="submit" name="change" value="数量変更">
                         </form>
                     </td>
                     <td><?php print number_format($carts['carts_in_item'] * $carts['price']) ?></td>
                     <td>
                         <form method="post">
                             <input type='hidden' name="item_id" value="<?php print htmlspecialchars($carts['item_id'], ENT_QUOTES) ?>">
                             <input type="submit" name="delete" value="商品を削除">
                         </form>
                     </td>
                 </tr>
             <?php } ?>
             <tr>
                 <td colspan="4"></td>
                 <td><?php print number_format($sum); ?></td>
             </tr>
         </table>
         <form action="bought.php">
             <input type="submit" value="購入する" class="buy_button">
         </form>
     <?php } else { ?>
         <p>カートに商品はありません</p>
     <?php } ?>
     <footer>
         <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
     </footer>
 </body>

 </html>