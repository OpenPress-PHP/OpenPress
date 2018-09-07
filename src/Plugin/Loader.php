<?php
namespace OpenPress\Plugin;

use OpenPress\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class Loader
{
    private $plugins = [];
    private $loaded;

    public function __construct(Application $app)
    {
        $finder = (new Finder())->files()->in(__DIR__ . "/../../app/plugins/*")->name("composer.json");
        foreach ($finder as $file) {
            $plugin = json_decode(file_get_contents($file->getPathName()), true);
            $enabled = $plugin['extra']['openpress']['enabled'] ?? false;
            unset($plugin['extra']['openpress']['enabled']);
            $baseClass = $plugin['extra']['openpress']['class'] ?? null;
            unset($plugin['extra']['openpress']['class']);

            $location = dirname(realpath($file->getPathName()));

            if ($baseClass === null) {
                throw new InvalidPluginException($location, "Missing plugin class");
            }

            if (!in_array(Plugin::class, class_parents($baseClass))) {
                throw new InvalidPluginException($location, "Plugin class must extend " . Plugin::class);
            }

            if (!isset($plugin['name'])) {
                throw new InvalidPluginException($location, "Missing name");
            }

            $data = [
                "name" => $plugin['name'],
                "version" => $plugin['version'] ?? "1.0.0",
                "description" => $plugin['description'] ?? "",
                "authors" => $plugin['authors'] ?? [],
                "location" => $location,
                "enabled" => $enabled,
                "extra" => $plugin['extra']['openpress'] ?? []
            ];

            $this->plugins[$plugin['name']] = new $baseClass($app->getContainer(), $data);
        }
    }

    public function loadPlugins()
    {
        $this->loaded = true;
        foreach ($this->plugins as $name => $plugin) {
            $plugin->load();
        }
    }

    public function getAllPlugins()
    {
        return $this->plugins;
    }

    public function getEnabledPlugins()
    {
        return array_filter($this->plugins, function ($plugin) {
            return $plugin->isEnabled();
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
            $directory = $plugin->getLocation() . "/" . ($plugin->getExtraPluginInformation()[$extraKey] ?? $folder);
            if ($filesystem->exists($directory)) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }
}
