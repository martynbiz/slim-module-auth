<?php
// DIC configuration

$container = $app->getContainer();

// Models
$container['auth.model.user'] = function ($c) {
    return new MartynBiz\Slim\Modules\Auth\Model\User();
};

$container['auth'] = function ($c) {
    $settings = $c->get('settings')['auth'];
    $authAdapter = new MartynBiz\Slim\Modules\Auth\Adapter\Mongo( $c['auth.model.user'] );
    return new MartynBiz\Slim\Modules\Auth\Auth($authAdapter, $settings);
};
