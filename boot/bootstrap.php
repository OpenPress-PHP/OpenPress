<?php
use OpenPress\Application;
use OpenPress\Config\Configuration;

require_once __DIR__ . "/../vendor/autoload.php";

$app = new Application();

$app->getContainer()->get("database");
