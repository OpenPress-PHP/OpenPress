<?php
namespace OpenPress\Content;

use DI\ContainerBuilder;
use OpenPress\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class Loader
{
    private $app;
    private static $plugins = [];
    private static $pluginsByPriority = [];
    private static $themes = [];
    private static $loaded;

    public function __construct(Application $app)
    {
        $this->app = $app;

        if (empty(static::$plugins)) {
            $finder = (new Finder())->files()->in(__DIR__ . "/../../app/plugins/*")->name("composer.json");
            foreach ($finder as $file) {
                $plugin = json_decode(file_get_contents($file->getPathName()), true);

                $enabled = $plugin['extra']['openpress']['enabled'] ?? false;
                unset($plugin['extra']['openpress']['enabled']);

                $baseClass = $plugin['extra']['openpress']['class'] ?? null;
                unset($plugin['extra']['openpress']['class']);

                $priority = (int) ($plugin['extra']['openpress']['priority'] ?? 1);
                unset($plugin['extra']['openpress']['priority']);

                $location = dirname(realpath($file->getPathName()));

                if (!$enabled) {
                    $baseClass = DisabledPlugin::class;
                }

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
                    "priority" => $priority,
                    "extra" => $plugin['extra']['openpress'] ?? []
                ];

                $clazz = new $baseClass($data);
                static::$plugins[$plugin['name']] = $clazz;
                if ($enabled) {
                    static::$pluginsByPriority[$priority][] = $clazz;
                }
            }

            krsort(static::$pluginsByPriority);
        }

        if (empty(static::$themes)) {
            $finder = (new Finder())->files()->in(__DIR__ . "/../../app/themes/*")->name("composer.json");
            foreach ($finder as $file) {
                $theme = json_decode(file_get_contents($file->getPathName()), true);
                $enabled = $theme['extra']['openpress']['enabled'] ?? false;
                unset($theme['extra']['openpress']['enabled']);

                $location = dirname(realpath($file->getPathName()));

                if (!isset($theme['name'])) {
                    throw new InvalidThemeException($location, "Missing name");
                }

                $data = [
                    "name" => $theme['name'],
                    "version" => $theme['version'] ?? "1.0.0",
                    "description" => $theme['description'] ?? "",
                    "authors" => $theme['authors'] ?? [],
                    "location" => $location,
                    "enabled" => $enabled,
                    "extra" => $theme['extra']['openpress'] ?? []
                ];

                static::$themes[$theme['name']] = new Theme($data);
            }
        }
    }

    public function createContainer(ContainerBuilder $builder)
    {
        foreach ($this->getEnabledPlugins() as $name => $plugin) {
            $plugin->createContainer($builder);
        }
    }

    public function loadPlugins()
    {
        static::$loaded = true;
        foreach ($this->getEnabledPlugins() as $name => $plugin) {
            $plugin->setContainer($this->app->getContainer());
            $plugin->load();
        }
    }

    public function getAllPlugins()
    {
        return static::$plugins;
    }

    public function getAllThemes()
    {
        return static::$themes;
    }

    public function getEnabledPlugins()
    {
        return array_filter(static::$plugins, function ($plugin) {
            return $plugin->isEnabled();
        });
    }

    public function getEnabledTheme()
    {
        $themesEnabled = array_filter(static::$themes, function ($theme) {
            return $theme->isEnabled();
        });

        if (count($themesEnabled) == 0) {
            throw new RuntimeException("No theme enabled");
        }

        return array_shift($themesEnabled);
    }

    public function getViewDirectories()
    {
        return array_merge($this->getDirectoriesFromEnabledTheme("Views"), $this->getDirectoriesFromEnabledPlugins("Views"));
    }

    public function getMigrationDirectories()
    {
        return $this->getDirectoriesFromEnabledPlugins("Migrations");
    }

    public function getSeedDirectories()
    {
        return $this->getDirectoriesFromEnabledPlugins("Seeds");
    }

    private function getDirectoriesFromEnabledPlugins($key)
    {
        $directories = [];
        $filesystem = new Filesystem();
        foreach (static::$pluginsByPriority as $priority => $plugins) {
            foreach ($plugins as $plugin) {
                $directory = call_user_func([$plugin, "get{$key}Directory"]);
                if ($filesystem->exists($directory)) {
                    $directories[] = $directory;
                }
            }
        }

        return $directories;
    }

    private function getDirectoriesFromEnabledTheme($key)
    {
        $filesystem = new Filesystem();
        $theme = $this->getEnabledTheme();
        $directory = call_user_func([$theme, "get{$key}Directory"]);

        if (!$filesystem->exists($directory)) {
            $directory = [];
        } else {
            $directory = [$directory];
        }

        return $directory;
    }
}
