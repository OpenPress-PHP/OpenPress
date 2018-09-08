<?php
namespace OpenPress;

use DI\Bridge\Slim\App;
use DI\ContainerBuilder;
use OpenPress\Content\Loader;
use OpenPress\Config\Configuration;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application extends App
{
    public static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            throw new RuntimeException("You cannot access the App instance before it has been instantiated");
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();

        static::$instance = $this;
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
                return new Loader($this);
            },
            EventDispatcher::class => function (ContainerInterface $c) {
                return new EventDispatcher();
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

        $loader = new Loader($this);
        $loader->createContainer($builder);
    }
}
