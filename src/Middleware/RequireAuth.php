<?php
namespace MartynBiz\Slim\Module\Auth\Middleware;

use Slim\Container;

class RequireAuth
{
    /**
     * @var Slim\Container $container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Attach to routes to ensure protected pages
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $attributes = $this->container->get('martynbiz-auth.auth')->getAttributes();
        $currentUser =  $this->container->get('martynbiz-auth.model.user')->where('id', $attributes['id'])->first();

        if (!$currentUser) {
            $loginUrl = $this->container->get('router')->pathFor('auth_session_login');
            return $response->withRedirect($loginUrl, 302);
        }

        $response = $next($request, $response);

        return $response;
    }
}
