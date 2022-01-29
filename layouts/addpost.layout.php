    <?php // needed $maxSizeOfUploadImage ?>
    <div class='addpostcontainer'>
        <div class='addpostmsg'>
            <?php
                if (isset($_GET['msg'])) {
                    $msg = clearStr($_GET['msg']);
                    if ($msg == "Пост добавлен") {
                        echo "<p class='ok'>$msg</p>";
                    } else {
                        echo "<p class='error'>$msg</p>";
                    }
                }
            ?>
        </div>
        <p class='addpostlabel'>Форма добавления поста:</p>
        <div class='addpostform'>
            <form action='/addpost' method='post' enctype="multipart/form-data" id='addpost'>
                <label id='input' for='addpostname' class='addpost'>Заголовок: </label>
                <input type='text' id='addpostname' title='Заголовок' class='addpostname' required minlength="1" maxlength='140' autofocus name='addPostZag' placeholder="Добавьте заголовок поста. Количество символов: от 20 до 140">
                
                <br> <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxSizeOfUploadImage ?>"> <br>
                <label id='img' for='file_img' class='addpost'>Пожалуйста, добавьте картинку. Допускаются jpg весом до <?= $maxSizeOfUploadImage/1024/1024 ?> мегабайт</label>
                <input class='addpostimg' type='file' name='addPostImg' id='file_img' > <!-- required -->
                <br>
                <br>
                <label id='input' for='addposttextarea' class='addpost'>Содержание поста: </label>
                <br><textarea class='text' title='Содержание' required minlength="1" maxlength='4000' spellcheck="true"  wrap='hard' name='addPostContent' placeholder="Добавление содержания. Количество символов: от 20 до 4000 с пробелами" id='addposttextarea'></textarea><br>
                
                <input type='submit' value='Добавить пост' class='addpostsubmit'>
            </form>
        </div>
    </div>
