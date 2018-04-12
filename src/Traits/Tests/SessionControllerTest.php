<?php
namespace MartynBiz\Slim\Module\Auth\Traits\Tests;

trait SessionControllerTests
{
    public function testGetLoginShowsFormWhenNotAuthenticated()
    {
        $response = $this->runApp('GET', '/session/login');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#login_form', (string)$response->getBody()); // has form
    }

    public function testGetLoginRedirectsWhenAuthenticated()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/session/login');

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLoginWithValidCredentials()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/session/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLoginWithInvalidCredentials()
    {
        $response = $this->runApp('POST', '/session/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#login_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetLogoutShowsFormWhenAuthenticated()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/session/logout');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('.content-wrapper form#logout_form', (string)$response->getBody()); // has form
    }

    public function testGetLogoutRedirectsWhenAuthenticated()
    {
        $response = $this->runApp('GET', '/session/logout');

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLogout()
    {
        $this->login( $this->user );

        // mock authenticate to return true
        $this->app->getContainer()['auth']
            ->expects( $this->once() )
            ->method('clearAttributes');

        $response = $this->runApp('POST', '/session/logout', [
            '_METHOD' => 'DELETE',
        ]);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }
}
