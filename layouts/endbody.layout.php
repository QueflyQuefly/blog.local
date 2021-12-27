
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?= date("Y", time()) ?><br> Время загрузки страницы: <?= round(microtime(true) - $this->startTime, 4) ?> с.</p>
</footer>
</body>
</html>