<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class TeamVoter
 * @package App\Security\Voter
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class TeamVoter extends Voter
{
    const VIEW = 'TEAM_VIEW';
    const EDIT = 'TEAM_EDIT';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!\in_array($attribute, [self::VIEW, self::EDIT], true)) {
            return false;
        }

        if (!$subject instanceof Team) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        throw new \LogicException('Non available case');
    }

    /**
     * @param Team $team
     * @param User $user
     * @return bool
     */
    private function canView(Team $team, User $user): bool
    {
        if ($this->canEdit($team, $user)) {
            return true;
        }

        return false;
    }

    /**
     * @param Team $team
     * @param User $user
     * @return bool
     */
    private function canEdit(Team $team, User $user): bool
    {
        return $user === $team->getOwner();
    }
}