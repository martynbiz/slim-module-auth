<?php
namespace MartynBiz\Slim\Module\Auth\Adapter;

interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     */
    public function authenticate($identity, $password);
}
