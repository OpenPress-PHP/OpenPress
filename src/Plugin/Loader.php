<?php
namespace OpenPress\Plugin;

use Symfony\Component\Finder\Finder;

class Loader
{
    private $plugins = [];

    public function __construct()
    {
        $finder = (new Finder())->files()->in(__DIR__ . "/../../app/plugins/*")->name("composer.json");
        foreach ($finder as $file) {
            $plugin = json_decode(file_get_contents($file->getPathName()), true);
            $enabled = isset($plugin['extra']['openpress']['enabled']) ? $plugin['extra']['openpress']['enabled'] : false;
            unset($plugin['extra']['openpress']['enabled']);

            $this->plugins[$plugin['name']] = [
                "name" => $plugin['name'],
                "version" => isset($plugin['version']) ? $plugin['version'] : "1.0.0",
                "description" => isset($plugin['description']) ? $plugin['description'] : "",
                "authors" => isset($plugin['authors']) ? $plugin['authors'] : [],
                "enabled" => $enabled,
                "extra" => isset($plugin['extra']['openpress']) ? $plugin['extra']['openpress'] : []
            ];
        }

        dd($this->plugins);
    }
}
