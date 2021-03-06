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
        $settings = $container->get('settings')['martynbiz-auth'];

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
        ->add($container->get('csrf'))
        ->add(new Auth\Middleware\RememberMe($container));
    }

    /**
     * Load is run last, when config, dependencies, etc have been initiated
     * Routes ought to go here
     * @param App $app
     * @return void
     */
    public function postInit(App $app)
    {
        $container = $app->getContainer();

        // add events for this module
        $container->get('events')->register('martynbiz-core:tests:setup', function($app, $testCase) {

            $container = $app->getContainer();

            // auth service
            $container['martynbiz-auth.auth'] = $testCase->getMockBuilder('MartynBiz\\Slim\\Module\\Auth\\Auth')
                ->disableOriginalConstructor()
                ->getMock();
        });
    }

    /**
     * Copies files from vendor dir to project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function copyFiles($dirs)
    {
        // copy module settings and template
        $src = __DIR__ . '/../files/modules/*';
        shell_exec("cp -rn $src {$dirs['modules']}");

        // copy db migrations
        $src = __DIR__ . '/../files/db/*';
        shell_exec("cp -rn $src {$dirs['db']}");
    }

    /**
     * Removes files from the project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function removeFiles($dirs)
    {
        // remove module settings and template
        if ($path = realpath("{$dirs['modules']}/martynbiz-auth")) {
            shell_exec("rm -rf $path");
        }

        // TODO inform to manually remove db migrations coz they'll fuck up rollback
    }
}
