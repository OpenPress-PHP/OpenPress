<?php
use OpenPress\Plugin\Loader;
use Composer\Console\Application;
use OpenPress\Config\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

$loader = (Application::getInstance())->getContainer()->get(Loader::class);

return [
    'paths' => [
        'migrations' => $loader->getMigrationDirectories(),
        'seeds' => $loader->getSeedDirectories()
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
