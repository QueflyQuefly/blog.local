<?php
$startTime = microtime(true);
require 'FactoryMethod.php';
$frontController = new FrontController($_SERVER['REQUEST_URI'], $_REQUEST, $startTime, new FactoryMethod());