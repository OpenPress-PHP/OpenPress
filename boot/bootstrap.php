<?php
use OpenPress\Http\Route;
use OpenPress\Application;

require_once __DIR__ . "/../vendor/autoload.php";

$app = new Application();

/**
 * Setup Eloquent Database
 */
$app->getContainer()->get("database");

/**
 * Define routes
 */
Route::register();

return $app;
