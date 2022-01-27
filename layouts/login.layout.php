    <div class='formcenter'>
        <div class='formform'>
            <p class='formlabel'>Вход</p>
            <form action='login.php' method='post'>
                <input type='email' name='email' required minlength="1" maxlength='50' autofocus autocomplete="on" placeholder='Ваш email' class='formtext'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' autocomplete="off" placeholder='Ваш пароль' class='formtext'><br>
                <img src="noise-picture.php">
                <input type='login' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='formtext'><br>
                <div class='formmsg'>
                <p class='formerror'>
                        <?php
                            if (!empty($msg)) {
                                echo $msg;
                            }
                        ?>
                    </p>
                </div>
                <div id='left'><a class='formbutton' href='reg'>Создать аккаунт</a></div>
                <div id='right'><input type='submit' value='Войти' class='formsubmit'></div>
            </form>
        </div>
    </div> 