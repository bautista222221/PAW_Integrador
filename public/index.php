<?php

ob_start();

session_start();

require __DIR__ . '/../src/bootstrap.php';

$router->dirigir($request);

ob_end_flush();