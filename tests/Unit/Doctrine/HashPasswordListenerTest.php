<?php

namespace App\Tests\Unit\Doctrine;

use App\Doctrine\HashPasswordListener;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserTest
 * @package App\Tests\Unit\Entity
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class HashPasswordListenerTest extends TestCase
{
    /**
     * @var HashPasswordListener
     */
    private $listener;
    /**
     * @var UserPasswordEncoderInterface|ObjectProphecy
     */
    private $encoder;
    /**
     * @var User
     */
    private $user;
    /**
     * @var LifecycleEventArgs|ObjectProphecy
     */
    private $args;

    public function setUp()
    {
        $this->encoder = $this->prophesize(UserPasswordEncoderInterface::class);
        $this->listener = new HashPasswordListener($this->encoder->reveal());
        $this->user = new User();

        $this->args = $this->prophesize(LifecycleEventArgs::class);
    }

    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        $this->assertEquals(
            [
                Events::prePersist,
                Events::preUpdate,
            ],
            $this->listener->getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function prePersistDoNothing()
    {
        $this->args->getEntity()->shouldBeCalled()->willReturn(null);

        $this->listener->prePersist($this->args->reveal());
    }

    /**
     * @test
     */
    public function prePersistNotChangeUser()
    {
        $this->user->setPlainPassword('');
        $this->args->getEntity()->shouldBeCalled()->willReturn($this->user);

        $this->listener->prePersist($this->args->reveal());
    }

    /**
     * @test
     */
    public function prePersistChangeUser()
    {
        $plainPassword = 'asdf';
        $this->user->setPlainPassword($plainPassword);
        $this->args->getEntity()->shouldBeCalled()->willReturn($this->user);

        $encoded = '$encoded$encoded$encoded$encoded$encoded';
        $this->encoder->encodePassword($this->user, $plainPassword)->shouldBeCalled()->willReturn($encoded);

        $this->listener->prePersist($this->args->reveal());

        $this->assertEquals($encoded, $this->user->getPassword());
    }

    /**
     * @test
     */
    public function preUpdateDoNothing()
    {
        $this->args->getEntity()->shouldBeCalled()->willReturn(null);

        $this->listener->preUpdate($this->args->reveal());
    }

    /**
     * @test
     */
    public function preUpdateNotChangeUser()
    {
        $this->user->setPlainPassword('');
        $this->args->getEntity()->shouldBeCalled()->willReturn($this->user);

        $this->listener->preUpdate($this->args->reveal());
    }

    /**
     * @test
     */
    public function preUpdateChangeUser()
    {
        $plainPassword = 'asdf';
        $this->user->setPlainPassword($plainPassword);

        $this->args->getEntity()->shouldBeCalled()->willReturn($this->user);
        $em = $this->prophesize(EntityManagerInterface::class);
        $this->args->getEntityManager()->shouldBeCalled()->willReturn($em);

        $doctrineClassMeta = $this->prophesize(ClassMetadata::class);
        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $em->getClassMetadata(User::class)->shouldBeCalled()->willReturn($doctrineClassMeta->reveal());
        $em->getUnitOfWork()->shouldBeCalled()->willReturn($unitOfWork->reveal());

        $unitOfWork->recomputeSingleEntityChangeSet($doctrineClassMeta->reveal(), $this->user)->shouldBeCalled();

        $encoded = '$encoded$encoded$encoded$encoded$encoded';
        $this->encoder->encodePassword($this->user, $plainPassword)->shouldBeCalled()->willReturn($encoded);

        $this->listener->preUpdate($this->args->reveal());

        $this->assertEquals($encoded, $this->user->getPassword());
    }
}
