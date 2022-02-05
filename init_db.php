<?php
$pathToDbService = 'services' . DIRECTORY_SEPARATOR . 'DbService.php';
require_once $pathToDbService;
define('RIGHTS_SUPERUSER', 'superuser');
DbService::getInstance();