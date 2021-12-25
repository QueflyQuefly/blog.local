<?php
$startTime = microtime(true);
require 'FactoryMethod.php';
$factoryMethod = new FactoryMethod();
$frontController = new FrontController($_SERVER['REQUEST_URI'], $_REQUEST, $startTime, $factoryMethod);