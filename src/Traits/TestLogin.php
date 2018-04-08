<?php
namespace MartynBiz\Slim\Module\Auth\Traits;

/**
 *
 */
trait TestLogin
{
    public function login($user)
    {
        $container = $this->app->getContainer();

        // return an identity (eg. email)
        $container->get('martynbiz-auth.auth')
            ->method('getAttributes')
            ->willReturn( $user->toArray() );

        // by defaut, we'll make isAuthenticated return a false
        $container->get('martynbiz-auth.auth')
            ->method('isAuthenticated')
            ->willReturn(true);
    }
}
