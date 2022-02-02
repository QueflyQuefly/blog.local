<?php
$startTime = microtime(true);
require 'Factory.php';
$frontController = new FrontController($_SERVER['REQUEST_URI'], $_REQUEST, $startTime, new Factory());