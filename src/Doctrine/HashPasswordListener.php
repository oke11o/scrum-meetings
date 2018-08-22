<?php

namespace App\Doctrine;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class HashPasswordListener
 * @package App\Doctrine
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class HashPasswordListener implements EventSubscriber
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var User $user */
        $user = $args->getEntity();
        if (!($user instanceof User)) {
            return;
        }

        $this->encodePassword($user);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        /** @var User $user */
        $user = $args->getEntity();
        if (!($user instanceof User)) {
            return;
        }

        if ($this->encodePassword($user)) {
            // necessary to force the update to see the change
            $em = $args->getEntityManager();
            $meta = $em->getClassMetadata(get_class($user));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $user);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    private function encodePassword(User $user): bool
    {
        if (!$user->getPlainPassword()) {
            return false;
        }

        $encoded = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);

        return true;
    }
}