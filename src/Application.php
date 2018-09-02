<?php
namespace OpenPress;

use DI\Bridge\Slim\App;
use DI\ContainerBuilder;
use OpenPress\Plugin\Loader;
use OpenPress\Config\Configuration;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class Application extends App
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        $definitions = [
            // Slim Settings
            'settings.displayErrorDetails' => Configuration::get("debug", false),

            // Container
            'database' => function (ContainerInterface $c) {
                $capsule = new Capsule();

                $capsule->addConnection(Configuration::get("database"));

                $capsule->setAsGlobal();
                $capsule->bootEloquent();
            },

            // DI Injections
            Loader::class => function (ContainerInterface $c) {
                return new Loader();
            },
            \Slim\Views\Twig::class => function (ContainerInterface $c) {
                $cache = Configuration::get("cache", false);
                if ($cache === true) {
                    $cache = __DIR__ . "/cache";
                }

                $twig = new \Slim\Views\Twig($c->get(Loader::class)->getViewDirectories(), [
                    'cache' => $cache,
                    'debug' => Configuration::get("debug", false)
                ]);

                $twig->addExtension(new \Slim\Views\TwigExtension(
                    $c->get('router'),
                    $c->get('request')->getUri()
                ));

                return $twig;
            },
        ];

        $builder->addDefinitions($definitions);
    }
}
