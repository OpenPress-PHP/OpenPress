<?php
use OpenPress\Application;
use OpenPress\Content\Loader;
use OpenPress\Config\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

$loader = (Application::getInstance())->getContainer()->get(Loader::class);

return [
    'paths' => [
        'migrations' => array_merge([__DIR__ . "/app/db/migrations"], $loader->getMigrationDirectories()),
        'seeds' => array_merge([__DIR__ . "/app/db/seeds"], $loader->getSeedDirectories())
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'database',
        'database' => [
            'name' => Configuration::get("database.database"),
            'connection' => Capsule::connection()->getPdo(),
        ],
    ],
    'version_order' => 'creation'
];
