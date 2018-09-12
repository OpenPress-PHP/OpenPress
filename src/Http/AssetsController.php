<?php
namespace OpenPress\Http;

use Slim\Http\Request;
use Slim\Http\Response;
use InvalidArgumentException;
use OpenPress\Content\Loader;
use MatthiasMullie\Minify\JS as JSMinifier;

class AssetsController extends BaseController
{
    public function js(Request $request, Response $response, Loader $loader)
    {
        $bundle = $request->getQueryParam("bundle");

        $bundles = $loader->getBundles();
        if (!isset($bundles[$bundle])) {
            throw new InvalidArgumentException("{$bundle} does not exist with any bundle.json file.");
        }

        $minifer = new JSMinifier();
        foreach ($bundles as $bundle) {
            $minifer->add($bundle);
        }

        $response->withHeader("Content-Type", "application/javascript");
        return $response->getBody()->write($minifer->minify());
    }
}
