<?php
use OpenPress\Config\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . "/../vendor/autoload.php";

// Setup connection to database
$capsule = new Capsule();

$capsule->addConnection(Configuration::get("database"));

$capsule->setAsGlobal();
$capsule->bootEloquent();
