    <?php // needed $forAdmin ?>
    <div class='formentry'>
        <p class='formname'>Регистрация</p>
        <form action='/reg' method='post'>
            <label class='formlabel'>Введите вашу почту: 
                <input type='regemail' name='regemail' required autofocus minlength="1" maxlength='50'  autocomplete="on" placeholder='Введите regemail' class='formtext'>
            </label><br>
            <label class='formlabel'>Введите код с картинки выше: 
                <input type='login' name='regfio' required minlength="1" maxlength='50' autocomplete="on" placeholder='ФИО или псевдоним' class='formtext'>
            </label><br>
            <label class='formlabel'>Придумайте пароль: 
                <input type='regpassword' name='regpassword' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите пароль' class='formtext'>
            </label><br>
            <img src="/noise-picture.php"><br>
            <label class='formlabel'>Введите код с картинки выше: 
                <input type='text' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='formtext'>
            </label><br>
            <?php
                if (!empty($forAdmin)) {
                    echo $forAdmin;
                }
            ?>
            <div class='formmsg'>
                <p class='error'>
                    <?php
                        if (!empty($_GET['msg'])) {
                            echo clearStr($_GET['msg']);
                        }
                    ?>
                </p>
            </div>
            <div id='right'>
                <input type='submit' value='Создать аккаунт' class='formsubmit'>
            </div>
        </form>
    </div>
