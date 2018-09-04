<?php
namespace OpenPress\Plugin;

use RuntimeException;
use OpenPress\Application;

abstract class Plugin
{
    protected $app;

    /* Attributes are read only */
    private $name = null;
    private $version = null;
    private $description = null;
    private $authors = null;
    private $location = null;
    private $enabled = null;
    private $extra = null;

    public function __construct(Application $app, array $data)
    {
        $this->app = $app;

        $this->setData("name", $data['name']);
        $this->setData("version", $data['version'] ?? "1.0.0");
        $this->setData("description", $data['description'] ?? "");
        $this->setData("authors", $data['authors'] ?? []);
        $this->setData("location", $data['location']);
        $this->setData("enabled", $data['enabled']);
        $this->setData("extra", $data['extra']['openpress'] ?? []);
    }

    abstract public function load();

    private function setData($key, $value)
    {
        if ($this->{$key} !== null) {
            throw new RuntimeException("Cannot redefine readonly value $key");
        }

        $this->{$key} = $value;
    }

    /***** Getters *****/

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getExtraPluginInformation()
    {
        return $this->extra;
    }
}
