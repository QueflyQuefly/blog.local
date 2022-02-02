
        <div class='formentry'>
            <p class='formname'>Администрирование</p>
            <form action='/admin'  method='post'>
                <div class='formradio'>
                    <input type='radio' id='radio1' name='view' value='viewUsers' class='radio' checked>
                    <label class='formlabel' for='radio1'>К управлению пользователями</label>

                    <br><input type='radio' id='radio2' name='view' value='viewPosts' class='radio'>
                    <label class='formlabel' for='radio2'>К управлению постами</label>

                    <br><input type='radio' id='radio3' name='view' value='addAdmin' class='radio'>
                    <label class='formlabel' for='radio3'>Добавить администратора</label>

                    <br><input type='radio' id='radio4' name='view' value='viewStab' class='radio'>
                    <label class='formlabel' for='radio4'>К стабу базы данных</label>
                </div>
                <br><div id='right'><input type='submit' value='Перейти' class='formsubmit'></div>
            </form>
        </div>
