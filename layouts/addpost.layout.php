
    <div class='centerpost'>
        <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
        <div class='msg'>
            <?php
                if (isset($_GET['msg'])) {
                    $msg = clearStr($_GET['msg']);
                    if ($msg == "Пост добавлен") {
                        $msg = "<p class='ok'>$msg</p>";
                    } else {
                        $msg = "<p class='error'>$msg</p>";
                    }
                }
            ?>
        </div>
        <p class='label'>Форма добавления поста:</p>
        <div class='form'>
            <form action='addpost.php' method='post' enctype="multipart/form-data" id='addpost'>
                <label id='input' for='addpostname' class='addpost'>Заголовок: </label>
                <input type='text' id='addpostname' title='Заголовок' class='addpostname' required minlength="1" maxlength='140' autofocus name='addPostZag' placeholder="Добавьте заголовок поста. Количество символов: от 20 до 140">
                
                <br> <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxSizeOfUploadImage ?>"> <br>
                <label id='img' for='file_img' class='addpost'>Пожалуйста, добавьте картинку. Допускаются jpg весом до <?= $maxSizeOfUploadImage ?> байт</label>
                <input class='addpostimg' type='file' name='addPostImg' id='file_img' > <!-- required -->
                <br>
                <br>
                <label id='input' for='content' class='addpost'>Содержание поста: </label>
                <br><textarea class='text' title='Содержание' required minlength="1" maxlength='4000' spellcheck="true"  wrap='hard' name='addPostContent' placeholder="Добавление содержания. Количество символов: от 20 до 4000 с пробелами" id='content'></textarea><br>
                
                <input type='submit' value='Добавить пост' class='addpostsubmit'>
            </form>
        </div>
    </div>
