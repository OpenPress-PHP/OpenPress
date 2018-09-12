<?php
namespace OpenPress\Render\Twig;

use Slim\Router;
use Slim\Http\Request;
use OpenPress\Content\Loader;

class BundlerExtension extends \Twig_Extension
{
    private $router;
    private $loader;
    private $request;

    public function __construct(Router $router, Loader $loader, Request $request)
    {
        $this->router = $router;
        $this->loader = $loader;
        $this->request = $request;
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('css', [$this, "css"]),
            new \Twig_Function('js', [$this, "js"]),
        ];
    }

    public function css($bundle)
    {
        if ($bundle == null) {
            $bundle = substr($this->request->getUri()->getPath(), 1);
        }

        return "<link rel='stylesheet' src='" . $this->router->pathFor('assets.css') . "?bundle={$bundle}'>";
    }

    public function js($bundle = null)
    {
        if ($bundle == null) {
            $bundle = substr($this->request->getUri()->getPath(), 1);
        }

        return "<script src='" . $this->router->pathFor('assets.js') . "?bundle={$bundle}'></script>";
    }
}
