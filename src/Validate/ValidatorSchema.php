<?php
namespace OpenPress\Validate;

use InvalidArgumentException;
use OpenPress\Content\Plugin;
use Symfony\Component\Yaml\Yaml;
use Respect\Validation\Validator;

class ValidatorSchema
{
    private static $jsonValidators = [];
    private static $jsonValidatorsStripped = [];

    private static $validators;

    private $rules;

    public function __construct(string $schema)
    {
        $this->rules = static::parse($schema);
    }

    public function get(string $field)
    {
        return $this->rules[$field];
    }

    public function getFields()
    {
        return array_keys($this->rules);
    }

    public function parseForServer(Validator $v, $name, $params, $data)
    {
        return static::$validators[$name]["server"]($v, $params, $data);
    }

    public function getClientValidator($name)
    {
        return static::$validators[$name]["client"];
    }

    public function getValidators()
    {
        return array_keys(static::$validators);
    }

    public function toJson()
    {
        return json_encode($this->rules);
    }

    private static function parse($validator)
    {
        $file = null;
        $names = array_keys(static::$jsonValidators);
        if (in_array($validator, $names)) {
            $file = static::$jsonValidators[$validator];
        } else {
            $strippedNames = array_keys(static::$jsonValidatorsStripped);
            if (in_array($validator, $strippedNames)) {
                $defined = static::$jsonValidatorsStripped[$validator];

                if (count($defined) > 1) {
                    throw new InvalidArgumentException($validator . " must be more specific. (" . implode(", " . $defined) . ")");
                } else {
                    $file = $defined[0];
                }
            }
        }

        if ($file === null) {
            throw new InvalidArgumentException($validator . " not found.");
        }
        $contents = file_get_contents($file);

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $rules = [];
        if ($ext === "json") {
            $rules = json_decode($contents, true);
        } elseif ($ext === "yaml") {
            $rules = Yaml::parse($contents);
        }

        if (in_array("@extends", array_keys($rules))) {
            $ext = $rules["@extends"];
            unset($rules["@extends"]);
            $rules = array_merge($rules, self::parse($ext));
        }

        return $rules;
    }

    public static function addValidatorDefinition(string $name, callable $php, string $js)
    {
        self::$validators[$name] = [
            "server" => $php,
            "client" => $js
        ];
    }

    public static function addJsonValidator(Plugin $plugin, string $filename, string $path)
    {
        self::$jsonValidators[pathinfo($filename, PATHINFO_FILENAME) . "@" . $plugin->getName()] =
            self::$jsonValidatorsStripped[pathinfo($filename, PATHINFO_FILENAME)][] = $path;
    }
}
