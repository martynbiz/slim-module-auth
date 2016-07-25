<?php
namespace MartynBiz\Slim\Module\Auth\Controller;

use MartynBiz\Slim3Controller\Controller;
use MartynBiz\Slim\Module\Auth\Exception\InvalidReturnToUrl;
use MartynBiz\Slim\Module\Auth\Model\Account;

abstract class BaseController extends \MartynBiz\Slim\Module\Core\Controller\BaseController
{
    /**
     * Will ensure that returnTo url is valid before doing redirect. Otherwise mean
     * people could use out login then redirect to a phishing site
     * @param string $returnTo The returnTo url that we want to check against our white list
     */
    protected function returnTo($returnTo)
    {
        $container = $this->getContainer();
        $settings = $container->get('settings');

        // check returnTo
        $host = parse_url($returnTo, PHP_URL_HOST);
        if ($host and !preg_match($settings['valid_return_to'], $host)) {
            throw new InvalidReturnToUrl( $container->get('i18n')->translate('invalid_return_to') );
        }

        return $container->get('response')->withRedirect($returnTo);
    }

    // /**
    //  * Will do the Core render, but attach the currentUser
    //  * @param string $file Name of the template/ view to render
    //  * @param array $args Additional variables to pass to the view
    //  * @param Response?
    //  */
    // public function render($file, $data=array())
    // {
    //     $container = $this->getContainer();
    //
    //     if ($container->has('auth')) {
    //         $data['currentUser'] = $container->get('auth')->getCurrentUser();
    //     }
    //
    //     return parent::render($file, $data);
    // }
}
