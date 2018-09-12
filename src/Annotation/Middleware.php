<?php
namespace OpenPress\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Middleware
{
    /**
     * @var string
     */
    public $object;
}
