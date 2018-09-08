<?php
namespace OpenPress\Content;

use InvalidArgumentException;

class InvalidPluginException extends InvalidArgumentException
{
    public $name;
    public $reason;

    public function __construct($name, $reason)
    {
        parent::__construct("$name is invalid. {$reason}");
        $this->name = $name;
        $this->reason = $reason;
    }
}
