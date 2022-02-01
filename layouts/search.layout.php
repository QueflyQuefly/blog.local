    <?php // needed $search ?>
    <div class='contentsinglepost'>
        <div class='search'>
            <form class='search' action='search.php' method='get'>
                <input class='text' type='text' id='search' required autofocus autocomplete="on" minlength="1" maxlength="100" placeholder='Найти...' name='search' value='<?= $search ?>'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 
    </div>
