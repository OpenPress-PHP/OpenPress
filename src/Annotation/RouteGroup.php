<?php
namespace OpenPress\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RouteGroup
{
    /**
     * @Required
     * @var string
     */
    public $group;
}
