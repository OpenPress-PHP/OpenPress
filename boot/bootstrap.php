<?php
use OpenPress\Http\Route;
use OpenPress\Application;
use OpenPress\Plugin\Loader;

require_once __DIR__ . "/../vendor/autoload.php";

$app = new Application();

/**
 * Setup Eloquent Database
 */
$app->getContainer()->get("database");

/**
 * Load enabled plugins
 */
$app->getContainer()->get(Loader::class)->loadPlugins();

/**
 * Define routes
 */
Route::setApplication($app);
Route::register();

return $app;
