<?php
    //DBへの接続
    $dsn = 'データベース名';
    $user = "ユーザ名";
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING));

    //初期値
    $submit_value = "投稿";
    $name_value = "";
    $comment_value = "";
    $edit_id = NULL;
    $message = "　";

    // //DB削除 使う時のみ
    // $sql = 'DROP TABLE bbs';
    // $stmt = $pdo->query($sql);

    //tableを作る
    $sql = "CREATE TABLE IF NOT EXISTS bbs"
    ."("
    ."id INT AUTO_INCREMENT PRIMARY KEY,"
    ."name char(32),"
    ."comment TEXT,"
    ."password char(32),"
    ."datetime DATETIME,"
    ."updatedatetime DATETIME"
    .");";
    $stmt = $pdo->query($sql);

    //データベースの操作
    if (count($_POST) > 0){
        //投稿もしくは更新
        if (!empty($_POST["name"]) && !empty($_POST["comment"]) && isset($_POST["submit"])) {
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $password = $_POST["password"];
            $datetime = date("Y-m-d H:i:s");
            //投稿
            if (empty($_POST["update-id"])) {
                $sql = "INSERT INTO bbs (name, comment, password, datetime, updatedatetime) VALUES (:name, :comment, :password, :datetime, :updatedatetime)";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                $stmt -> bindParam(':updatedatetime', $datetime, PDO::PARAM_STR);
                $stmt -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
                $message = "投稿しました。";
            }
            //更新
            else {
                $id = $_POST["update-id"];
                $sql = 'UPDATE bbs SET name=:name, comment=:comment, password=:password, updatedatetime=:updatedatetime WHERE id=:id';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
                $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                $stmt -> bindParam(':updatedatetime', $datetime, PDO::PARAM_STR);
                $message = "更新しました。";
            }
            $stmt -> execute();
        }
        //削除
        elseif (!empty($_POST["del-id"]) && isset($_POST["del-submit"])) {
            $del_id = $_POST["del-id"];
            $entered_pass = $_POST["del-pass"];
            $sql = "SELECT * FROM bbs WHERE id=:deleteId";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(':deleteId', $del_id, PDO::PARAM_INT);
            $stmt -> execute();
            $result = $stmt -> fetchAll();
            $target_id = $result[0]['password'];
            if ($target_id == $entered_pass) {
                $sql = 'DELETE FROM bbs WHERE id=:id';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':id', $del_id, PDO::PARAM_INT);
                $stmt -> execute();
                $message = "削除しました。";
            } else {
                $message =  "入力されたパスワードが正しくありません。";
            }
        }
        //編集
        elseif (!empty($_POST["edit-id"]) && !empty($_POST["edit-pass"]) && isset($_POST["edit-submit"])) {
            $submitValue = "更新";
            $edit_id = $_POST["edit-id"];
            $entered_pass = $_POST["edit-pass"];
            $sql = "SELECT * FROM bbs WHERE id=:editId";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(':editId', $edit_id, PDO::PARAM_INT);
            $stmt -> execute();
            $result = $stmt -> fetchAll();
            $target_id = $result[0]['password'];
            if ($target_id == $entered_pass) {
                $name_value = $result[0]['name'];
                $comment_value = $result[0]['comment'];
                $message = "内容を編集してください。";
            } else {
                $message =  "入力されたパスワードが正しくありません。";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>掲示板 with MySQL</title>
        <style>
            body {
                color: #6B3C32;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .form-wrapper {
                display: flex;
            }
            .form {
                margin: 20px 20px;
                width: 200px;
                text-align: center;
            	background-color: #F9E9E5;
            	padding: 20px;
            	border-radius: 20px;
            }
            .form-part{
                margin: 2px;
            }
            h4 {
                text-align: center;
                margin: 0;
            }
            input {
                width: 100%;
            }
            .input-wrapper {
                margin: 10px 0;
            }
            .btn {
                width: 50px;
                display: block;
                margin: 10px auto;
            }
            .items {
                width: 100%;
                display: flex;
            	flex-wrap: wrap;
            	padding: 10px;
            }
            .item {
                width: 20%;
            	background-color: #F9E9E5;
            	margin: 20px 5%;
            	padding: 20px;
            	border-radius: 20px;
            	text-align: center;
            }
            .description {
                background-color: #bf8377;
                width: 60%;
                color: white;
                border-radius: 4px;
                text-align: center;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <h2>🎧好きなアーティストは？🎧</h2>
        <form action="" method="post" class="form-wrapper">
            <div class="post-form form">
                <h4><投稿></h4>
                <div class="input-wrapper">
                    <label for="name">名前</label>
                    <br>
                    <input type="text" name="name" id="name" placeholder="名前" class="form-part" value=<?php echo $name_value ?>>
                </div>
                <div class="input-wrapper">
                    <label for="comment">コメント</label>
                    <br>
                    <textarea name="comment" cols="25" rows="4" id="comment" placeholder="好きなアーティスト" class="form-part"><?php echo $comment_value ?></textarea>
                </div>
                <div class="input-wrapper">
                <label for="password">パスワード</label>
                <br>
                <input type="text" name="password" id="password" placeholder="お好きなパスワード" class="form-part">
                </div>
                <input type="submit" name="submit" value=<?php echo $submit_value ?> class="form-part btn">
                <input type="hidden" name="update-id" value=<?php echo $edit_id ?>>
            </div>
            <div class="delete-form form">
                <h4><削除></h4>
                <div class="input-wrapper">
                    <label for="del-id">投稿id</label>
                    <br>
                    <input type="number" name="del-id" id="del-id" placeholder="削除するid" class="form-part">
                </div>
                <div class="input-wrapper">
                    <label for="del-pass">投稿時のパスワード</label>
                    <br>
                    <input type="text" name="del-pass" id="del-pass" placeholder="投稿時のパスワード" class="form-part">
                </div>
                <input type="submit" name="del-submit" value="削除" class="form-part btn">
            </div>
            <div class="edit-form form">
                <h4><編集></h4>
                <div class="input-wrapper">
                    <label for="edit-id">投稿id</label>
                    <br>
                    <input type="number" name="edit-id" id="edit-id" placeholder="編集するid" class="form-part">
                </div>
                <div class="input-wrapper">
                    <label for="edit-pass">投稿時のパスワード</label>
                    <br>
                    <input type="text" name="edit-pass" id="edit-pass" placeholder="投稿時のパスワード" class="form-part">
                </div>
                <input type="submit" name="edit-submit" value="編集" class="form-part btn">
            </div>
        </form>
        <?php 
            echo "<p>".$message."</p>";
        ?>
    </body>
</html>

<?php
    //テーブルの表示
    $sql = 'SELECT * FROM bbs';
    $stmt = $pdo -> query($sql);
    $results = $stmt -> fetchAll();
    echo "<div class='items'>";
    foreach($results as $row) {
        echo "<div class='item'>";
            echo "<p class='description'>id</p>";
            echo "<p>".$row['id']."</p>";
            echo "<p class='description'>名前</p>";
            echo "<p>".$row['name']."</p>";
            echo "<p class='description'>コメント</p>";
            echo "<p>".$row['comment']."</p>";
            echo "<p class='description'>投稿日</p>";
            echo "<p>".$row['datetime']."</p>";
            echo "<p class='description'>最終更新日</p>";
            echo "<p>".$row['updatedatetime']."</p>";
        echo "</div>";
    }
    echo "<div>";
?>