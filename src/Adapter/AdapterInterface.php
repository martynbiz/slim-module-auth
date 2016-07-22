<?php
namespace MartynBiz\Slim\Modules\Auth\Adapter;

interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     */
    public function authenticate($identity, $password);
}
