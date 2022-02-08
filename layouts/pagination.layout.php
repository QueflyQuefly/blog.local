
<?php // needed $nameOfPath:string, $numberOfPosts:int and $page:int ?>
        <div class='singleposttext'>
            <label for='number'>Количество: </label>
            <select id='number' class='select' name="number" onchange="window.location.href=this.options[this.selectedIndex].value">
                <option value='$numberOfPosts' selected><?= $numberOfPosts ?></option>
                <option value='/<?= $nameOfPath ?>/?number=25&page=<?= $page ?>'>25</option>
                <option value='/<?= $nameOfPath ?>/?number=50&page=<?= $page ?>'>50</option>
                <option value='/<?= $nameOfPath ?>/?number=100&page=<?= $page ?>'>100</option>
            </select>

            <label for='page'>Страница: </label>
            <select id='page' class='select' name="page" onchange="window.location.href=this.options[this.selectedIndex].value">
                <option value='<?= $page ?>' selected><?= $page ?></option>
                <?php
                    for ($i = $page - 3; $i <= $page + 3; $i++) {
                        if ($i > 0) {
                            echo "<option value='/$nameOfPath/?number=$numberOfPosts&page=$i'>$i</option>";
                        }
                    }
                ?>
            </select>
        </div>
