<?php
require_once 'Factory.php';
$factory = new Factory();
$stabService = $factory->getStabService();
$numberOfIterations = 10;
$stabService->stabDb($numberOfIterations);