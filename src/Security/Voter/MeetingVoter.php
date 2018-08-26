<?php

namespace App\Security\Voter;

use App\Entity\Meeting;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class MeetingVoter
 * @package App\Security\Voter
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingVoter extends Voter
{
    public const EDIT = 'MEETING_EDIT';
    public const VIEW = 'MEETING_VIEW';

    protected function supports($attribute, $subject)
    {
        if (!\in_array($attribute, [self::EDIT, self::VIEW], true)) {
            return false;
        }

        if (!$subject instanceof Meeting) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
        }

        throw new \LogicException('Non available case');
    }

    private function canEdit(Meeting $meeting, User $user)
    {
        return $user === $meeting->getTeam()->getOwner();
    }

    private function canView(Meeting $meeting, User $user)
    {
        if ($this->canEdit($meeting, $user)) {
            return true;
        }

        return $meeting->getTeam()->getUsers()->contains($user);
    }

}