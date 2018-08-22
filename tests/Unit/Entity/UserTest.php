<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Class UserTest
 * @package App\Tests\Unit\Entity
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class UserTest extends TestCase
{
    /**
     * @var
     */
    private $user;

    public function setUp()
    {
        $this->user = new User();
    }
    /**
     * @test
     */
    public function tmp()
    {
        $user = $this->user;

        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $email = 'email@example.com';
        $user->setEmail($email);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->getUsername());

        $this->assertEquals('', $user->getSalt());

        $password = 'pass';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());

        $plainPass = 'plain';
        $user->setPlainPassword($plainPass);
        $this->assertEquals($plainPass, $user->getPlainPassword());
        $this->assertEquals(null, $user->getPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());

    }
}
