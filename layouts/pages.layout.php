<?php // needed $nameOfPath:string, $page:int ?>
        <p style='padding-left:3vmin'><span>Страницы:</span></p>
        <ul style='display: inline-flex;'>
        <?php
            for ($i = $page - 3; $i <= $page + 3; $i++) {//обманываю пользователя, что есть ещё страницы
                if ($i > 0) {
                    echo "<li style='list-style-type:none'><a class='menuLink' href='/$nameOfPath/page=$i'>$i</a></li>";
                }
            }
        ?>
        </ul>
        <hr>
