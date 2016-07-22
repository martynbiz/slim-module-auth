<?php
namespace MartynBiz\Slim\Modules\Core;

use Slim\App;
use Slim\Container;
use Slim\Http\Headers;
use MartynBiz\Mongo\Connection;
use MartynBiz\Slim\Modules\Core\Http\Request;
use MartynBiz\Slim\Modules\Core\Http\Response;

class Module
{
    /**
     * Get config array for this module
     * @return array
     */
    public function initDependencies(Container $container)
    {
        $settings = $container->get('settings');

        // Models
        $container['auth.model.user'] = function ($c) {
            return new MartynBiz\Slim\Modules\Auth\Model\User();
        };

        $container['auth'] = function ($c) {
            $settings = $c->get('settings')['auth'];
            $authAdapter = new MartynBiz\Slim\Modules\Auth\Adapter\Mongo( $c['auth.model.user'] );
            return new MartynBiz\Slim\Modules\Auth\Auth($authAdapter, $settings);
        };
    }

    /**
     * Initiate app middleware (route middleware should go in initRoutes)
     * @param App $app
     * @return void
     */
    public function initMiddleware(App $app)
    {

    }

    /**
     * Load is run last, when config, dependencies, etc have been initiated
     * Routes ought to go here
     * @param App $app
     * @return void
     */
    public function initRoutes(App $app)
    {
        $container = $app->getContainer();

        $app->group('/auth', function () {

            $this->post('',
                '\MartynBiz\Slim\Modules\Auth\Controller\SessionController:post')->setName('auth_session_post');
            $this->delete('',
                '\MartynBiz\Slim\Modules\Auth\Controller\SessionController:delete')->setName('auth_session_delete');
            $this->get('/login',
                '\MartynBiz\Slim\Modules\Auth\Controller\SessionController:index')->setName('auth_session_login');
            $this->get('/logout',
                '\MartynBiz\Slim\Modules\Auth\Controller\SessionController:index')->setName('auth_session_logout');

            $this->get('/register',
                '\MartynBiz\Slim\Modules\Auth\Controller\UsersController:create')->setName('auth_users_create');
            $this->post('/register',
                '\MartynBiz\Slim\Modules\Auth\Controller\UsersController:post')->setName('auth_users_post');
            $this->get('/resetpassword',
                '\MartynBiz\Slim\Modules\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password');
            $this->post('/resetpassword',
                '\MartynBiz\Slim\Modules\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password_post');
        });

        // admin routes -- invokes auth middleware
        $app->group('/admin', function () {

            // admin/users routes
            $this->group('/users', function () {

                $this->get('',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:index')->setName('admin_users');
                $this->get('/{id:[0-9]+}',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:show')->setName('admin_users_show');
                $this->get('/create',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:create')->setName('admin_users_create');
                $this->get('/{id:[0-9]+}/edit',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:edit')->setName('admin_users_edit');

                $this->put('/{id:[0-9]+}',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:update')->setName('admin_users_update');
                $this->delete('/{id:[0-9]+}',
                    '\MartynBiz\Slim\Modules\Auth\Controller\Admin\UsersController:delete')->setName('admin_users_delete');

            })->add( new Middleware\RoleAccess($this->getContainer(), [ User::ROLE_ADMIN ]) );

        })->add( new Middleware\Auth( $container['auth'] ) );
    }
}
