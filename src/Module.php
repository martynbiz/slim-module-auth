<?php
namespace MartynBiz\Slim\Module\Auth;

use Slim\App;
use Slim\Container;
use MartynBiz\Slim\Module\ModuleInterface;

use MartynBiz\Slim\Module\Auth;
use MartynBiz\Slim\Module\Core;

class Module implements ModuleInterface
{
    /**
     * Get config array for this module
     * @return array
     */
    public function initDependencies(Container $container)
    {
        $container['martynbiz-auth.model.user'] = function ($c) {
            return new Auth\Model\User();
        };

        $container['martynbiz-auth.auth'] = function ($c) {
            $settings = $c->get('settings')['martynbiz-auth'];
            $authAdapter = new Auth\Adapter\Eloquent( $c['martynbiz-auth.model.user'] );
            return new Auth\Auth($authAdapter, $settings);
        };
    }

    /**
     * Initiate app middleware (route middleware should go in initRoutes)
     * @param App $app
     * @return void
     */
    public function initMiddleware(App $app)
    {
        $container = $app->getContainer();

        $app->add(new Auth\Middleware\CurrentUser($container));
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
        $settings = $container->get('settings')['auth'];

        $app->group($settings['base_path'], function () use ($app, $container) {

            $app->group('/session', function () use ($app) {
                $app->post('',
                    '\MartynBiz\Slim\Module\Auth\Controller\SessionController:post')->setName('auth_session_post');
                $app->delete('',
                    '\MartynBiz\Slim\Module\Auth\Controller\SessionController:delete')->setName('auth_session_delete');
                $app->get('/login',
                    '\MartynBiz\Slim\Module\Auth\Controller\SessionController:index')->setName('auth_session_login');
                $app->get('/logout',
                    '\MartynBiz\Slim\Module\Auth\Controller\SessionController:index')->setName('auth_session_logout');
            });

            // $app->group('/users', function () use ($app) {
            //     $app->get('/register',
            //         '\MartynBiz\Slim\Module\Auth\Controller\UsersController:register')->setName('auth_users_register');
            //     $app->post('/register',
            //         '\MartynBiz\Slim\Module\Auth\Controller\UsersController:post')->setName('auth_users_post');
            //     $app->get('/resetpassword',
            //         '\MartynBiz\Slim\Module\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password');
            //     $app->post('/resetpassword',
            //         '\MartynBiz\Slim\Module\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password_post');
            // });

            // admin routes -- invokes auth middleware
            $app->group('/admin', function () {

                // admin/users routes
                $this->group('/users', function () {

                    $this->get('',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:index')->setName('admin_users');
                    $this->get('/{id:[0-9]+}',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:show')->setName('admin_users_show');
                    $this->get('/create',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:create')->setName('admin_users_create');
                    $this->get('/{id:[0-9]+}/edit',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:edit')->setName('admin_users_edit');

                    $this->put('/{id:[0-9]+}',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:update')->setName('admin_users_update');
                    $this->delete('/{id:[0-9]+}',
                        '\MartynBiz\Slim\Module\Auth\Controller\Admin\UsersController:delete')->setName('admin_users_delete');

                });

            })
            ->add( new Auth\Middleware\RequireAuth($container) )
            ->add( new Auth\Middleware\RoleAccess($container, [ Auth\Model\User::ROLE_ADMIN ]) );
        })
        ->add(new Auth\Middleware\RememberMe($container));
        // ->add(new Core\Middleware\Csrf($container));
    }

    /**
     * Copies files from vendor dir to project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function copyFiles($dest)
    {
        $src = __DIR__ . '/../modules/*';
        shell_exec("cp -rn $src $dest");
    }

    /**
     * Removes files from the project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function removeFiles($dest)
    {
        if ($path = realpath("$dest/martynbiz-auth")) {
            shell_exec("rm -rf $path");
        }
    }
}
