<?php
namespace OpenPress\Http;

use ReflectionClass;
use RuntimeException;
use OpenPress\Application;
use OpenPress\Event\EventDictionary;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Route
{
    private static $app = null;
    private static $group = null;

    public static function setApplication(Application $app)
    {
        if (static::$app !== null) {
            throw new RuntimeException("Application has already been defined");
        }

        static::$app = static::$group = $app;
    }

    public static function register()
    {
        $dispatcher = static::$app->getContainer()->get(EventDispatcher::class);
        $dispatcher->dispatch(EventDictionary::ROUTE);
    }

    /**
     * Add GET route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function get($pattern, $callable)
    {
        return static::map(['GET'], $pattern, $callable);
    }

    /**
     * Add POST route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function post($pattern, $callable)
    {
        return static::map(['POST'], $pattern, $callable);
    }

    /**
     * Add PUT route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function put($pattern, $callable)
    {
        return static::map(['PUT'], $pattern, $callable);
    }

    /**
     * Add PATCH route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function patch($pattern, $callable)
    {
        return static::map(['PATCH'], $pattern, $callable);
    }

    /**
     * Add DELETE route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function delete($pattern, $callable)
    {
        return static::map(['DELETE'], $pattern, $callable);
    }

    /**
     * Add OPTIONS route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function options($pattern, $callable)
    {
        return static::map(['OPTIONS'], $pattern, $callable);
    }

    /**
     * Add route for any HTTP method
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callable The route callback routine
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function any($pattern, $callable)
    {
        return static::map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $callable);
    }

    /**
     * Add route with multiple methods
     *
     * @param  string[] $methods  Numeric array of HTTP method names
     * @param  string   $pattern  The route URI pattern
     * @param  callable|string    $callable The route callback routine
     *
     * @return RouteInterface
     */
    public static function map(array $methods, $pattern, $callable)
    {
        return static::$group->map($methods, $pattern, $callable);
    }

    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string              $from
     * @param string|UriInterface $to
     * @param int                 $status
     *
     * @return RouteInterface
     */
    public static function redirect($from, $to, $status = 302)
    {
        $handler = function ($request, ResponseInterface $response) use ($to, $status) {
            return $response->withHeader('Location', (string)$to)->withStatus($status);
        };

        return static::get($from, $handler);
    }

    /**
     * Route Groups
     *
     * This method accepts a route pattern and a callback. All route
     * declarations in the callback will be prepended by the group(s)
     * that it is in.
     *
     * @param string   $pattern
     * @param callable $callable
     *
     * @return RouteGroupInterface
     */
    public static function group($pattern, $callable)
    {
        /** @var RouteGroup $group */
        $group = static::$app->getContainer()->get('router')->pushGroup($pattern, $callable);
        $group->setContainer(static::$app->getContainer());

        $nestedGroup = static::$group;
        $group(static::$app);
        static::$group = $nestedGroup;

        static::$app->getContainer()->get('router')->popGroup();
        return $group;
    }

    public static function controller(string $class)
    {
        if (!in_array(BaseController::class, class_parents($class))) {
            throw new RuntimeException("{$class} must extend " . BaseController::class);
        }

        $reader = new AnnotationReader();
        $reflectionClass = new ReflectionClass($class);
        $properties = $reflectionClass->getMethods();

        $groupDefined = false;

        $classAnnos = $reader->getClassAnnotations($reflectionClass);
        foreach ($classAnnos as $anno) {
            if (get_class($anno) === \OpenPress\Annotation\RouteGroup::class) {
                $group = static::$app->getContainer()->get('router')->pushGroup($anno->group, function () {
                });
                $group->setContainer(static::$app->getContainer());

                $nestedGroup = static::$group;
                $groupDefined = true;
            }

            if ($groupDefined && get_class($anno) === \OpenPress\Annotation\Middleware::class) {
                static::$group->add(static::$app->getContainer()->get($anno->object));
            }
        }

        foreach ($properties as $property) {
            $methodAnnos = $reader->getMethodAnnotations($property);
            $routes = [];
            foreach ($methodAnnos as $anno) {
                if (get_class($anno) === \OpenPress\Annotation\Route::class) {
                    $route = call_user_func_array([static::class, strtolower($anno->method)], [$anno->uri, [$class, $property->getName()]]);
                    if ($anno->name !== null) {
                        $route->setName($anno->name);
                    }

                    $routes[] = $route;
                } elseif (get_class($anno) === \OpenPress\Annotation\Middleware::class) {
                    foreach ($routes as $route) {
                        $route->add(static::$app->getContainer()->get($anno->object));
                    }
                }
            }
        }

        if ($groupDefined) {
            static::$group = $nestedGroup;
            static::$app->getContainer()->get('router')->popGroup();
        }
    }
}
