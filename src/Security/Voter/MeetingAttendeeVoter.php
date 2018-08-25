<?php

namespace App\Security\Voter;

use App\Entity\MeetingAttendee;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MeetingAttendeeVoter extends Voter
{
    public const EDIT = 'MEETING_ATTENDEE_EDIT';
    public const VIEW = 'MEETING_ATTENDEE_VIEW';

    protected function supports($attribute, $subject)
    {
        if (!\in_array($attribute, [self::VIEW, self::EDIT], true)) {
            return false;
        }

        if (!$subject instanceof MeetingAttendee) {
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

        return false;
    }

    private function canEdit(MeetingAttendee $attendee, User $user)
    {
        return $user === $attendee->getUser();
    }

    private function canView(MeetingAttendee $attendee, User $user)
    {
        if ($this->canEdit($attendee, $user)) {
            return true;
        }

        if (!$attendee->getMeeting()) {
            return false;
        }
        if (!$attendee->getMeeting()->getTeam()) {
            return false;
        }
        if (!$attendee->getMeeting()->getTeam()->getUsers()) {
            return false;
        }

        return $attendee->getMeeting()->getTeam()->getUsers()->contains($user);
    }
}
