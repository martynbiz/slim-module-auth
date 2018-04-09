<?php
namespace MartynBiz\Slim\Module\Auth\Traits\Tests;

class UsersControllerTests
{
    public function testGetRegister()
    {
        $response = $this->runApp('GET', '/users/register');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#register_form', (string)$response->getBody()); // has form
    }

    public function testPostRegisterWithValidData()
    {
        $response = $this->runApp('POST', '/users/register', static::getUserValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidUsersData
     */
    public function testPostRegisterWithInvalidData($firstName, $lastName, $email, $password, $agreement, $moreInfo)
    {
        $userValues = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'agreement' => $agreement,
            'more_info' => $moreInfo,
        ];
        if (!$agreement) unset($userValues['agreement']);

        $response = $this->runApp('POST', '/users/register', $userValues);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#register_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }



    private static function getUserValues($values=array())
    {
        return array_merge([
            'first_name' => 'Martyn',
            'last_name' => 'Bissett',
            'email' => 'martynbissett@yahoo.co.uk',
            'password' => 'T3st!ng123',
            'agreement' => '1',
            'more_info' => '',
        ], $values);
    }

    public function getInvalidUsersData()
    {
        return [
            static::getUserValues(['first_name' => '']),
            static::getUserValues(['last_name' => '']),
            static::getUserValues(['email' => '']),
            static::getUserValues(['email' => 'martyn']),
            static::getUserValues(['email' => 'martyn@']),
            static::getUserValues(['email' => 'martyn@yahoo']),
            static::getUserValues(['password' => '']),
            static::getUserValues(['password' => 'easypass']),
            static::getUserValues(['agreement' => null]),
            static::getUserValues(['more_info' => 'hello']),
        ];
    }
}
