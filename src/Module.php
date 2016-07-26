<?php
namespace MartynBiz\Slim\Module\Auth;

use Slim\App;
use Slim\Container;
use Slim\Http\Headers;
use MartynBiz\Mongo\Connection;
use MartynBiz\Slim\Module\Core\Http\Request;
use MartynBiz\Slim\Module\Core\Http\Response;
use MartynBiz\Slim\Module\ModuleInterface;
use MartynBiz\Slim\Module\Auth;

class Module implements ModuleInterface
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
            return new Auth\Model\User();
        };

        $container['auth'] = function ($c) {
            $settings = $c->get('settings')['auth'];
            $authAdapter = new Auth\Adapter\Mongo( $c['auth.model.user'] );
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

        $app->group('/auth', function () {

            $this->post('',
                '\MartynBiz\Slim\Module\Auth\Controller\SessionController:post')->setName('auth_session_post');
            $this->delete('',
                '\MartynBiz\Slim\Module\Auth\Controller\SessionController:delete')->setName('auth_session_delete');
            $this->get('/login',
                '\MartynBiz\Slim\Module\Auth\Controller\SessionController:index')->setName('auth_session_login');
            $this->get('/logout',
                '\MartynBiz\Slim\Module\Auth\Controller\SessionController:index')->setName('auth_session_logout');

            $this->get('/register',
                '\MartynBiz\Slim\Module\Auth\Controller\UsersController:create')->setName('auth_users_create');
            $this->post('/register',
                '\MartynBiz\Slim\Module\Auth\Controller\UsersController:post')->setName('auth_users_post');
            $this->get('/resetpassword',
                '\MartynBiz\Slim\Module\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password');
            $this->post('/resetpassword',
                '\MartynBiz\Slim\Module\Auth\Controller\UsersController:resetpassword')->setName('auth_users_reset_password_post');
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

            })->add( new Auth\Middleware\RoleAccess($this->getContainer(), [ Auth\Model\User::ROLE_ADMIN ]) );

        })->add( new Auth\Middleware\Auth( $container['auth'] ) );
    }

    /**
     * Copies files from vendor dir to project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function copyFiles($dest)
    {
        $src = __DIR__ . '/../files/*';
        shell_exec("cp -rn $src $dest");
    }

    /**
     * Removes files from the project tree
     * @param string $dest The root of the project
     * @return void
     */
    public function removeFiles($dest)
    {
        if (file_exists("$dest/src/autoload/settings.martynbiz-auth.php")) {
            shell_exec("rm $dest/src/autoload/settings.martynbiz-auth.php");
        }
        if (file_exists("$dest/templates/martynbiz-auth")) {
            shell_exec("rm -rf $dest/templates/martynbiz-auth");
        }
    }
}
