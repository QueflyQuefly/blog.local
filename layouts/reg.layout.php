    <div class='formentry'>
        <p class='formname'>Регистрация</p>
        <form action='/reg' method='post'>
            <input type='regemail' name='regemail' required autofocus minlength="1" maxlength='50'  autocomplete="on" placeholder='Введите regemail' class='formtext'><br>
            <input type='login' name='regfio' required minlength="1" maxlength='50' autocomplete="on" placeholder='ФИО или псевдоним' class='formtext'><br>
            <input type='regpassword' name='regpassword' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите пароль' class='formtext'><br>
            <img src="/noise-picture.php">
            <input type='text' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='formtext'><br>
            <?php
                if (!empty($forAdmin)) {
                    echo $forAdmin;
                }
            ?>
            <div class='formmsg'>
                <p class='formerror'>
                    <?php
                        if (!empty($_GET['msg'])) {
                            echo clearStr($_GET['msg']);
                        }
                    ?>
                </p>
            </div>
            <div id='right'><input type='submit' value='Создать аккаунт' class='formsubmit'></div>
        </form>
    </div>