
    <div class='formentry'>
        <p class='formname'>Вход</p>
        <form action='/login' method='post'>
            <label class='formlabel'>Введите вашу почту: <input type='email' name='email' required minlength="1" maxlength='50' autofocus autocomplete="on" placeholder='Ваш email (до 50 символов)' class='formtext'></label>
            <label class='formlabel'>Придумайте пароль: <input type='password' name='password' required minlength="1" maxlength='20' autocomplete="off" placeholder='Ваш пароль (до 20 символов)' class='formtext'></label>
            <img class='captcha' src="/noise-picture.php"><br>
            <label class='formlabel'>Введите код с картинки выше: <input type='login' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Код с captcha' class='formtext'></label>
            <div class='formmsg'>
                <p class='error'>
                    <?php
                        if (!empty($_GET['msg'])) {
                            echo clearStr($_GET['msg']);
                        }
                    ?>
                </p>
            </div>
            <div id='left'><a class='formbutton' href='/reg'>Создать аккаунт</a></div>
            <div id='right'><input type='submit' value='Войти' class='formsubmit'></div>
        </form>
    </div>
