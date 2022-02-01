            <?php // needed $user ?>
            <div class='viewcomment'>
                <div class='form'>
                    <form action='cabinet.php' method='post'>
                        <input type='email' name='change_email' required autofocus minlength="1" maxlength='50' autocomplete="on" placeholder='Введите новый email' class='text' value='<?= $user['change_email'] ?>'><br>
                        <input type='login' name='change_fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='Новый псевдоним' class='text' value='<?= $user['change_fio'] ?>'><br>
                        <input type='password' name='change_password' minlength="0" maxlength='20' autocomplete="new-password" placeholder='Новый пароль; оставьте пустым, если не хотите менять' class='text'><br>
                        <div class='formmsg'>
                            <p class='error'>
                                <?php
                                    if (!empty($_GET['msg'])) {
                                        echo clearStr($_GET['msg']);
                                    }
                                ?>
                            </p>
                        </div>
                        <div id='right'><input type='submit' style='margin-left:5vmin' value='Сохранить' class='submit'></div>
                    </form>
                </div>
            </div>
