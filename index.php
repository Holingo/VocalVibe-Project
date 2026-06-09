<?php

require_once 'Routing.php';

date_default_timezone_set('Europe/Warsaw');

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::run($path);


