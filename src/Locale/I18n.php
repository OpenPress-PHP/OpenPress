<?php
namespace OpenPress\Locale;

use OpenPress\Application;
use OpenPress\Content\Loader;

class I18n
{
    private static $app;
    private static $locale;
    private static $localization = null;

    public static function setApplication(Application $app)
    {
        if (self::$app !== null) {
            throw new Exception("Cannot reset Application.");
        }

        self::$app = $app;
    }

    public static function setLocale(string $locale)
    {
        if (self::$locale !== null) {
            throw new Exception("Cannot reset locale.");
        }

        self::$locale = $locale;
    }

    public static function get($key)
    {
        if (self::$localization === null) {
            self::parseLanguageFiles();
        }

        return self::$localization[$key] ?? $key;
    }

    public static function exists($key)
    {
        if (self::$localization === null) {
            self::parseLanguageFiles();
        }

        return isset(self::$localization[$key]);
    }

    private static function parseLanguageFiles()
    {
        self::$localization = [];
        $files = self::$app->getContainer()->get(Loader::class)->getLanguageFiles(static::$locale);
        foreach ($files as $file) {
            $lines = explode("\n", $file);
            foreach ($lines as $line) {
                if (($line[0] ?? "#") == "#") {
                    continue;
                }

                $definition = explode("=", $line, 2);
                if (count($definition) >= 2) {
                    $key = trim($definition[0]);
                    $value = trim($definition[1]);

                    self::$localization[$key] = $value;
                }
            }
        }
    }
}
