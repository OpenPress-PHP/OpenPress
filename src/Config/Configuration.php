<?php
namespace OpenPress\Config;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    const CONFIG_FILE = __DIR__ . "/../../config.yml";

    private static $config = [];

    public static function get($key = "", $default = null)
    {
        self::boot();

        $config = self::$config;
        $sections = explode(".", $key);

        foreach ($sections as $section) {
            $config = isset($config[$section]) ? $config[$section] : $default;
            if ($config === $default) {
                break;
            }
        }

        return $config;
    }

    private static function boot()
    {
        if (empty(self::$config)) {
            self::$config = Yaml::parseFile(self::CONFIG_FILE);
        }
    }
}
