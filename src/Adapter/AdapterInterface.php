<?php
namespace MartynBiz\Slim\Module\Auth\Adapter;

interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     */
    public function authenticate($identity, $password);

    /**
     * This is the identity (e.g. username) stored for this user
     * @return string
     */
    public function getUserByEmail($email);
}
