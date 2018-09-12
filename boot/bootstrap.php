<?php
use OpenPress\Http\Route;
use OpenPress\Application;
use OpenPress\Content\Loader;
use OpenPress\Http\AssetsController;

session_start();

require_once __DIR__ . "/../vendor/autoload.php";
define("ROOT_DIR", realpath(__DIR__ . "/.."));

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

Route::group("/assets", function () {
    Route::get("/css", [AssetsController::class, "css"])->setName("assets.css");
    Route::get("/js", [AssetsController::class, "js"])->setName("assets.js");
});


return $app;
