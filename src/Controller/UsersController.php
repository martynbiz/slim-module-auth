<?php
namespace MartynBiz\Slim\Module\Auth\Controller\Admin;

use MartynBiz\Slim\Module\Auth\Controller\BaseController;
use MartynBiz\Slim\Module\Auth\Model\User;

class UsersController extends BaseController
{
    public function index($request, $response, $args)
    {
        $users = $this->get('auth.model.user')->find();

        return $this->render('auth/admin/users/index', [
            'users' => $users,
        ]);
    }

    /**
     * Upon creation too, the user will be redirect here to edit the user
     */
    public function edit($request, $response, $args)
    {
        $user = $this->get('auth.model.user')->findOneOrFail([
            'id' => (int) $id,
        ]);

        $user->set( $this->getPost() );

        return $this->render('auth/admin/users/edit', [
            'user' => $user,
        ]);
    }

    /**
     * This method will update the user (save draft) and 1) if xhr, return json,
     * or 2) redirect back to the edit page (upon which they can then submit when they
     * choose to)
     */
    public function update($request, $response, $args)
    {
        $user = $this->get('auth.model.user')->findOneOrFail([
            'id' => (int) $id,
        ]);

        $params = $this->getPost();

        // for security reasons, some properties are not on the whitelist but
        // we can directly assign them by this way
        if (isset($params['role'])) $user->role = $params['role'];

        if ( $user->save($params) ) {
            $this->get('flash')->addMessage('success', 'User saved.');
            return $this->redirect('/admin/users');
        } else {
            $this->get('flash')->addMessage('errors', $user->getErrors());
            return $this->forward('edit', $request, $response, $args);
        }
    }

    public function delete($request, $response, $args)
    {
        $user = $this->get('auth.model.user')->findOneOrFail(array(
            'id' => (int) $id,
        ));

        if ( $user->delete() ) {
            $this->get('flash')->addMessage('success', 'User deleted successfully');
            return $this->redirect('/admin/users');
        } else {
            $this->get('flash')->addMessage('errors', $user->getErrors());
            return $this->edit($id);
        }
    }
}
