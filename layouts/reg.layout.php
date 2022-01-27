    <div class='formcenter'>
        <div class='formform'>
            <p class='formlabel'>Регистрация</p>
            <form action='reg.php' method='post'>
                <input type='email' name='email' required autofocus minlength="1" maxlength='50'  autocomplete="on" placeholder='Введите email' class='formtext'><br>
                <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='ФИО или псевдоним' class='formtext'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите пароль' class='formtext'><br>
                <img src="noise-picture.php">
                <input type='text' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='formtext'><br>
                <?php
                    if (!empty($forAdmin)) {
                        echo $forAdmin;
                    }
                ?>
                <div class='formmsg'>
                    <p class='formerror'>
                        <?php
                            if (!empty($msg)) {
                                echo $msg;
                            }
                        ?>
                    </p>
                </div>
                <div id='right'><input type='submit' value='Создать аккаунт' class='formsubmit'></div>
            </form>
        </div>
    </div>