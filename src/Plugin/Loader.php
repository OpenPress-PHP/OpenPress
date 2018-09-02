<?php
namespace OpenPress\Plugin;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

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

            $location = dirname(realpath($file->getPathName()));

            if (!isset($plugin['name'])) {
                throw new InvalidPluginException($location, "Missing name.");
            }

            $this->plugins[$plugin['name']] = [
                "name" => $plugin['name'],
                "version" => $plugin['version'] ?? "1.0.0",
                "description" => $plugin['description'] ?? "",
                "authors" => $plugin['authors'] ?? [],
                "location" => $location,
                "enabled" => $enabled,
                "extra" => $plugin['extra']['openpress'] ?? []
            ];
        }
    }

    public function getAllPlugins()
    {
        return $this->plugins;
    }

    public function getEnabledPlugins()
    {
        return array_filter($this->plugins, function ($plugin) {
            return $plugin["enabled"];
        });
    }

    public function getViewDirectories()
    {
        return $this->getDirectoriesFromEnabledPlugins("views", "resources/views");
    }

    public function getMigrationDirectories()
    {
        return $this->getDirectoriesFromEnabledPlugins("migrations", "resources/migrations");
    }

    public function getSeedDirectories()
    {
        return $this->getDirectoriesFromEnabledPlugins("seeds", "resources/seeds");
    }

    private function getDirectoriesFromEnabledPlugins($extraKey, $folder)
    {
        $directories = [];
        $filesystem = new Filesystem();
        foreach ($this->getEnabledPlugins() as $plugin) {
            $directory = $plugin["location"] . "/" . ($plugin["extra"][$extraKey] ?? $folder);
            if ($filesystem->exists($directory)) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }
}
