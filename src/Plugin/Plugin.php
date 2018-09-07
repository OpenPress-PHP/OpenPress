<?php
namespace OpenPress\Plugin;

use RuntimeException;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

abstract class Plugin
{
    protected $container;

    /* Attributes are read only */
    private $name = null;
    private $version = null;
    private $description = null;
    private $authors = null;
    private $location = null;
    private $enabled = null;
    private $extra = null;

    public function __construct(array $data)
    {
        $this->setData("name", $data['name']);
        $this->setData("version", $data['version'] ?? "1.0.0");
        $this->setData("description", $data['description'] ?? "");
        $this->setData("authors", $data['authors'] ?? []);
        $this->setData("location", $data['location']);
        $this->setData("enabled", $data['enabled']);
        $this->setData("extra", $data['extra']['openpress'] ?? []);
    }

    public function createContainer(ContainerBuilder $builder)
    {
        // NO-OP
    }

    abstract public function load();

    public function setContainer(ContainerInterface $container)
    {
        if ($this->container !== null) {
            throw new RuntimeException("Cannot redefine container");
        }
        $this->container = $container;
    }

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
