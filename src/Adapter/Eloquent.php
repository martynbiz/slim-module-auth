<?php
namespace MartynBiz\Slim\Module\Auth\Adapter;

use MartynBiz\Slim\Module\Auth\Model\User;

class Eloquent implements AdapterInterface
{
    /**
     * @var string
     */
    protected $identity;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var App\Model\User
     */
    protected $model;

    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Performs an authentication attempt
     */
    public function authenticate($identity, $password)
    {
        // look up $user from the database
        $user = $this->model->where('email', $identity)
            ->orWhere('username', $identity)
            ->first();
        if (!$user) return false;

        return password_verify($password, $user->password);
    }

    /**
     * This is the identity (e.g. username) stored for this user
     * @return string
     */
    public function getUserByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }
}
