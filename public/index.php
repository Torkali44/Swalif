<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
| Namecheap layout (your server):
|   /home/USER/public_html/index.php  ← this file (document root)
|   /home/USER/Swalif/                ← Laravel app
|
| Local layout:
|   .../Swalif/public/index.php
|   .../Swalif/                       ← Laravel app
*/

$laravelRoot = dirname(__DIR__); // local: Swalif/

// Namecheap: public_html → ../Swalif
if (! is_file($laravelRoot.'/vendor/autoload.php')
    && is_file(__DIR__.'/../Swalif/vendor/autoload.php')) {
    $laravelRoot = __DIR__.'/../Swalif';
}

if (! is_file($laravelRoot.'/vendor/autoload.php')) {
    http_response_code(500);
    echo 'Laravel app not found (expected ../Swalif or parent folder).';
    exit(1);
}

if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelRoot.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelRoot.'/bootstrap/app.php';

// CRITICAL: document root is public_html, so uploads/assets resolve here
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
