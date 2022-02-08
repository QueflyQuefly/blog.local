<?php
$startTime = microtime(true);
require 'Factory.php';
$frontController = new FrontController(new Factory(), $startTime);