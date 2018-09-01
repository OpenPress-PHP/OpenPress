<?php
use OpenPress\Config\Configuration;
use Symfony\Component\Finder\Finder;
use Illuminate\Database\Capsule\Manager as Capsule;

try {
    $finder = (new Finder())->directories()->in(__DIR__ . "/app/plugins/*/db")->name("migrations");
} catch (InvalidArgumentException $e) {
    $finder = [];
}
$migrations = ["%%PHINX_CONFIG_DIR%%/db/migrations"];
foreach ($finder as $directory) {
    $migrations[] = $directory->getPathName();
}

try {
    $finder = (new Finder())->directories()->in(__DIR__ . "/app/plugins/*/db")->name("seeds");
} catch (InvalidArgumentException $e) {
    $finder = [];
}
$seeds = ["%%PHINX_CONFIG_DIR%%/db/seeds"];
foreach ($finder as $directory) {
    $seeds[] = $directory->getPathName();
}

return [
    'paths' => [
        'migrations' => $migrations,
        'seeds' => $seeds
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
