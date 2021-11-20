 <?php
    $error = []; //エラー文字格納用の配列
    $message = '';
    $host = 'localhost';
    $user_name = 'codecamp48798';
    $password = 'codecamp48798';
    $dbname = 'codecamp48798';
    $charset = 'utf8';
    $username = '';
    $userid = '';
    $item_id = '';
    $new_img_filename = '';

    //mySQL用の文字列
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



        $sql = 'SELECT * FROM carts INNER JOIN items ON carts.item_id = items.id WHERE user_id = ?';

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $stmt_contents = $stmt->fetchAll();


        $sum = 0;
        foreach ($stmt_contents as $item) {
            if ($item['carts_in_item'] > $item['stock']) {
                $error[] = htmlspecialchars($item['name'], ENT_QUOTES) . "の在庫が不足しています";
            }
            if ((int)$item['status'] === 0) {
                $error[] = htmlspecialchars($item['name'], ENT_QUOTES) . "は購入できない商品です";
            }
            $sum += $item['carts_in_item'] * $item['price'];
        }


        if (count($error) === 0) {
            try {
                $dbh->beginTransaction();
                foreach ($stmt_contents as $item) {
                    $sql = 'UPDATE items SET stock = stock -?,update_datetime = NOW() WHERE id = ?';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $item['carts_in_item'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $item['item_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                $sql = 'DELETE FROM carts WHERE user_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $userid, PDO::PARAM_INT);
                $stmt->execute();

                $dbh->commit();
            } catch (PDOException $e) {
                $dbh->rollback();
                throw $e;
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
     <title>購入完了</title>
     <link rel="stylesheet" href="/codeShop/css/bought.css">
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
     <h1>購入完了</h1>
     <?php if (count($error) === 0) { ?>
         <p>商品の購入が完了しました。</p>
         <p>ご購入ありがとうございました。</p>
     <?php } else { ?>
         <p>商品の購入に失敗しました</p>
         <?php foreach ($error as $message) { ?>
             <p>理由：<?php print $message ?></p>
         <?php } ?>
     <?php } ?>
     <table>
         <caption>購入した商品</caption>
         <tr>
             <th>商品名</th>
             <th>商品画像</th>
             <th>値段(税込)</th>
             <th>個数</th>
             <th>小計</th>
             　　
         </tr>
         <?php foreach ($stmt_contents as $carts) { ?>
             <tr>
                 <td><?php print htmlspecialchars($carts['name'], ENT_QUOTES) ?></td>
                 <td><img src="<?php print $img_dir . $carts['img']; ?>" class="bought_item_img"></td>
                 <td><?php print htmlspecialchars($carts['price'], ENT_QUOTES) ?></td>
                 <td><?php print htmlspecialchars($carts['carts_in_item'], ENT_QUOTES) ?></td>
                 <td><?php print number_format($carts['carts_in_item'] * $carts['price']) ?></td>
             </tr>
         <?php } ?>
         <tr>
             <td colspan="4"></td>
             <td><?php print number_format($sum); ?></td>
         </tr>
     </table>
     <form action="item_list_page.php">
         <input type="submit" value=">>商品一覧ページへ">
     </form>
     <footer>
         <p class="copy_right">Copryright&copy;ty All Rights Reserved.</p>
     </footer>
 </body>

 </html>