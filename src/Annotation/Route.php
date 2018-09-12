<?php
namespace OpenPress\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Route
{
    /**
     * @Required
     * @Enum({"GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS", "ANY"})
     */
    public $method;

    /**
     * @Required
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $name;
}
