<?php
namespace OpenPress\Http;

use Slim\Views\Twig;

class BaseController
{
    protected $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
}
