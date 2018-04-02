<?php
namespace MartynBiz\Slim\Module\Auth\Traits;

/**
 *
 */
trait GetCurrentUser
{
    /**
     * @var App\Model\User
     */
    protected $currentUser;

    /**
     * Get the current sign in user user
     * @param Request $request Not really needed here, api uses it though
     * @return User|null
     */
    protected function getCurrentUser()
    {
        // cache current user as a property
        if (! $this->currentUser) {
            $container = $this->getContainer();
            $attributes = $container->get('martynbiz-auth.auth')->getAttributes();
            $this->currentUser =  $container->get('model.user')->where('email', $attributes['email'])->first();
        }

        return $this->currentUser;
    }
}
