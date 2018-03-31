<?php
namespace MartynBiz\Slim\Module\Auth\Controller;

use MartynBiz\Slim\Module\Auth\Controller\BaseController;
use MartynBiz\Slim\Module\Auth\Model\User;
use MartynBiz\Slim\Module\Auth\AuthValidator;

class UsersController extends BaseController
{
    public function register($request, $response, $args)
    {
        // if errors found from post, this will contain data
        $params = $request->getParams();

        return $this->render('martynbiz-auth::users/register', [
            'params' => $params,
        ]);
    }

    public function post($request, $response, $args)
    {
        $params = $request->getParams();
        $container = $this->getContainer();
        $settings = $container->get('settings')['auth'];

        // validate form data

        // our simple custom validator for the form
        $validator = new AuthValidator( $container['martynbiz-auth.model.user'] );
        $validator->setData($params);
        $i18n = $container->get('i18n');

        // first_name
        $validator->check('first_name')
            ->isNotEmpty( $i18n->translate('first_name_missing') );

        // last_name
        $validator->check('last_name')
            ->isNotEmpty( $i18n->translate('last_name_missing') );

        // email
        $validator->check('email')
            ->isNotEmpty( $i18n->translate('email_missing') )
            ->isEmail( $i18n->translate('email_invalid') )
            ->isUniqueEmail( $i18n->translate('email_not_unique'), $container['martynbiz-auth.model.user'] );

        // password
        $message = $i18n->translate('password_must_contain');
        $validator->check('password')
            ->isNotEmpty($message)
            ->hasLowerCase($message)
            ->hasNumber($message)
            ->isMinimumLength($message, 8);

        // agreement
        $validator->check('agreement');

        // more_info
        // more info is a invisible field (not type=hidden, use css)
        // that humans won't see however, when bots turn up they
        // don't know that and fill it in. so, if it's filled in,
        // we know this is a bot
        if ($validator->has('more_info')) {
            $validator->check('more_info')
                ->isEmpty( $i18n->translate('email_not_unique') ); // misleading msg ;)
        }

        // if valid, create user

        if ($validator->isValid()) {

            if ($user = $container['martynbiz-auth.model.user']->create($params)) {

                // set meta entries (if given)
                if (isset($params['source'])) $user->setMeta('source', $params['source']);

                // set session attributes w/ backend (method of signin)
                $container->get('auth')->setAttributes( $user->toArray() );

                // // send welcome email
                // $container->get('mail_manager')->sendWelcomeEmail($user);

                // redirect
                return $response->withRedirect( $container->get('router')->pathFor($settings['redirect_after_register']) );

            } else {
                $errors = $user->errors();
            }

        } else {
            $errors = $validator->getErrors();
        }

        $container->get('flash')->addMessage('errors', $errors);
        return $this->register($request, $response, $args);
    }

    // /**
    //  * edit transaction form
    //  */
    // public function edit($request, $response, $args)
    // {
    //     $currentUser = $this->getCurrentUser();
    //
    //     $params = array_merge($currentUser->toArray(), $currentUser->getSettings(), $request->getParams());
    //
    //     return $this->render('martynbiz-auth::users/edit', [
    //         'params' => $params,
    //     ]);
    // }
    //
    // /**
    //  * edit transaction form action
    //  */
    // public function update($request, $response, $args)
    // {
    //     $container = $this->getContainer();
    //
    //     $params = $request->getParams();
    //     $currentUser = $this->getCurrentUser();
    //
    //     // validate form data
    //
    //     // our simple custom validator for the form
    //     $validator = new AuthValidator();
    //     $validator->setData($params);
    //     $i18n = $container->get('i18n');
    //
    //     // language
    //     $validator->check('language')
    //         ->isNotEmpty( $i18n->translate('language_missing') );
    //
    //     // // amount
    //     // $validator->check('amount')
    //     //     ->isNotEmpty( $i18n->translate('amount_missing') );
    //     //
    //     // // purchased at
    //     // $validator->check('purchased_at')
    //     //     ->isNotEmpty( $i18n->translate('purchased_at_missing') );
    //
    //     // if valid, create transaction
    //     if ($validator->isValid()) {
    //
    //         if ($currentUser->update($params)) {
    //
    //             $currentUser->setSettings($params);
    //
    //             // redirect
    //             return $response->withRedirect( $container->get('router')->pathFor('home') );
    //
    //         } else {
    //             $errors = $currentUser->errors();
    //         }
    //
    //     } else {
    //         $errors = $validator->getErrors();
    //     }
    //
    //     $container->get('flash')->addMessage('errors', $errors);
    //     return $this->edit($request, $response, $args);
    // }
    //
    // public function delete($request, $response, $args)
    // {
    //     $params = $request->getParams();
    //     $container = $this->getContainer();
    //
    //     $user = $container->get('model.user')->findOrFail((int)$args['user_id']);
    //
    //     // remove all transactions
    //     $transactions = $user->transactions;
    //     $transactions->remove();
    //
    //     // remove all funds
    //     $transactions = $user->transactions;
    //     $transactions->remove();
    //
    //     // remove all categories
    //     $categories = $user->categories;
    //     $categories->remove();
    //
    //     // remove all groups
    //     $groups = $user->groups;
    //     $groups->remove();
    //
    //     // remove all recoverTokens
    //     $recoverTokens = $user->recover_tokens;
    //     $recoverTokens->remove();
    //
    //     // remove all authTokens
    //     $authTokens = $user->auth_tokens;
    //     $authTokens->remove();
    //
    //     if ($user->delete()) {
    //
    //         // redirect
    //         return $response->withRedirect('users');
    //
    //     } else {
    //         $errors = $user->errors();
    //     }
    //
    //     $container->get('flash')->addMessage('errors', $errors);
    //     return $this->edit($request, $response, $args);
    // }
    //
    // /**
    //  *
    //  */
    // public function switchLanguage($request, $response, $args)
    // {
    //     $container = $this->getContainer();
    //     setcookie('language', $request->getParam('language'));
    //     return $response->withRedirect( $container->get('router')->pathFor('users_switch_language') );
    // }
}

// class UsersController extends BaseController
// {
//     public function index($request, $response, $args)
//     {
//         $users = $this->get('martynbiz-auth.model.user')->find();
//
//         return $this->render('martynbiz-auth::martynbiz-auth::admin/users/index', [
//             'users' => $users,
//         ]);
//     }
//
//     /**
//      * Upon creation too, the user will be redirect here to edit the user
//      */
//     public function edit($request, $response, $args)
//     {
//         $user = $this->get('martynbiz-auth.model.user')->findOneOrFail([
//             'id' => (int) $id,
//         ]);
//
//         $user->set( $this->getPost() );
//
//         return $this->render('martynbiz-auth::martynbiz-auth::admin/users/edit', [
//             'user' => $user,
//         ]);
//     }
//
//     /**
//      * This method will update the user (save draft) and 1) if xhr, return json,
//      * or 2) redirect back to the edit page (upon which they can then submit when they
//      * choose to)
//      */
//     public function update($request, $response, $args)
//     {
//         $user = $this->get('martynbiz-auth.model.user')->findOneOrFail([
//             'id' => (int) $id,
//         ]);
//
//         $params = $this->getPost();
//
//         // for security reasons, some properties are not on the whitelist but
//         // we can directly assign them by this way
//         if (isset($params['role'])) $user->role = $params['role'];
//
//         if ( $user->save($params) ) {
//             $this->get('flash')->addMessage('success', 'User saved.');
//             return $this->redirect('/admin/users');
//         } else {
//             $this->get('flash')->addMessage('errors', $user->getErrors());
//             return $this->forward('edit', $request, $response, $args);
//         }
//     }
//
//     public function delete($request, $response, $args)
//     {
//         $user = $this->get('martynbiz-auth.model.user')->findOneOrFail(array(
//             'id' => (int) $id,
//         ));
//
//         if ( $user->delete() ) {
//             $this->get('flash')->addMessage('success', 'User deleted successfully');
//             return $this->redirect('/admin/users');
//         } else {
//             $this->get('flash')->addMessage('errors', $user->getErrors());
//             return $this->edit($id);
//         }
//     }
// }
