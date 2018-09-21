<?php
namespace OpenPress\Http;

use Slim\Router;
use Slim\Views\Twig;

class BaseController
{
    protected $router;
    protected $twig;

    public function __construct(Router $router, Twig $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }
}
