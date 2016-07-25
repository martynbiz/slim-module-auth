<?php
/**
 * App middleware that will check if a user is authenticated or not, and set
 * the current user for the renderer to access
 */

namespace MartynBiz\Slim\Module\Auth\Middleware;

use Slim\Container;

class CurrentUser
{
    /**
     * @var Slim\Container $container
     */
    protected $auth;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        // attach current user to the template engine
        $this->container->get('renderer')->useData([
            'currentUser' => $this->container->get('auth')->getCurrentUser()
        ]);

        // pass onto the next callable
        $response = $next($request, $response);

        return $response;
    }
}
