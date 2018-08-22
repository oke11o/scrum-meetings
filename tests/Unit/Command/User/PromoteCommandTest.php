<?php

namespace App\Tests\Unit\Command\User;

use App\Command\User\PromoteCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class PromoteCommandTest
 * @package App\Tests\Unit\Command\User
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class PromoteCommandTest extends KernelTestCase
{
    private const EMAIL = 'email@email.ru';
    const ROLE = 'ROLE_NEW_ROLE';
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    private $em;
    /**
     * @var UserRepository|ObjectProphecy
     */
    private $userRepository;
    /**
     * @var PromoteCommand
     */
    private $command;
    /**
     * @var CommandTester
     */
    private $commandTester;

    public function customSetUp(User $user = null, bool $needFlush = false)
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->em->getRepository(User::class)->shouldBeCalled()->willReturn($this->userRepository->reveal());
        if ($needFlush) {
            $this->em->flush()->shouldBeCalled();
        }

        $this->userRepository->findOneByEmail(self::EMAIL)->shouldBeCalled()->willReturn($user);

        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add(new PromoteCommand($this->em->reveal()));

        $this->command = $application->find('app:user:promote');

        $this->commandTester = new CommandTester($this->command);
    }


    /**
     * @test
     */
    public function notFindUser()
    {
        $this->customSetUp(null);

        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'email' => self::EMAIL,
                'role' => 'rol',
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertContains(sprintf('Cannot find user with email "%s"', self::EMAIL), $output);
    }

    /**
     * @test
     */
    public function invalidRole()
    {
        $user = (new User())->setEmail(self::EMAIL);
        $this->customSetUp($user);

        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'email' => self::EMAIL,
                'role' => 'rol',
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertContains('Role must start `ROLE_`', $output);
    }

    /**
     * @test
     */
    public function execute()
    {
        $user = (new User())->setEmail(self::EMAIL);
        $this->customSetUp($user, true);

        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'email' => self::EMAIL,
                'role' => self::ROLE,
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertContains('You have success add role "ROLE_NEW_ROLE" to user with email', $output);
        $this->assertContains('"email@email.ru"', $output);
        $this->assertEquals(['ROLE_USER', self::ROLE], $user->getRoles());
    }

    /**
     * @test
     */
    public function executeAlreadyHave()
    {
        $user = (new User())->setEmail(self::EMAIL);
        $this->customSetUp($user, false);

        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'email' => self::EMAIL,
                'role' => 'ROLE_USER',
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertContains('User already have role "ROLE_USER"', $output);
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}
