<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Meeting;
use App\Entity\MeetingAttendee;
use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\MeetingAttendeeVoter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class MeetingAttendeeVoterTest
 * @package App\Tests\Unit\Security\Voter
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingAttendeeVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider examplesSupportAttributes
     */
    public function supportAttributes($attribute, $subject, bool $expect)
    {
        $voter = new MeetingAttendeeVoter();

        $reflection = new ReflectionObject($voter);
        $method = $reflection->getMethod('supports');
        $method->setAccessible(true);

        $result = $method->invoke($voter, $attribute, $subject);
        $this->assertEquals($expect, $result);
    }

    /**
     * @return array
     */
    public function examplesSupportAttributes()
    {
        return [
            'view attr support' => [
                'MEETING_ATTENDEE_EDIT',
                new MeetingAttendee('hash'),
                true,
            ],
            'unsupport subject' => [
                'MEETING_ATTENDEE_EDIT',
                new \stdClass(),
                false,
            ],
            'unsupport attribute' => [
                'TEAM_DELETE',
                new MeetingAttendee('hash'),
                false,
            ],
        ];
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Non available case
     */
    public function throwWhenUndefinedAttribute()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = new Team();

        $result = $method->invoke($voter, '', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function undefinedUserOnVoteOnAttribute()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = null;
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = new MeetingAttendee('asfd');

        $result = $method->invoke($voter, '', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canNotEditOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new MeetingAttendee('dsf'))->setUser($user2);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canEditOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new MeetingAttendee('sdf'))->setUser($user);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_EDIT', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canNotViewNullMeetingOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new MeetingAttendee('dsf'))->setUser($user2);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canNotViewNullTeamOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $meeting = new Meeting();
        $subject = (new MeetingAttendee('dsf'))->setUser($user2)->setMeeting($meeting);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canNotViewNullTeamUsersOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $team = new Team();
        $meeting = (new Meeting())->setTeam($team);
        $subject = (new MeetingAttendee('dsf'))->setUser($user2)->setMeeting($meeting);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canNotViewUsersOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $team = new Team();
        $team->addUser($user2);
        $meeting = (new Meeting())->setTeam($team);
        $subject = (new MeetingAttendee('dsf'))->setUser($user2)->setMeeting($meeting);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canViewOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $user2 = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $team = new Team();
        $team->addUser($user);
        $team->addUser($user2);
        $meeting = (new Meeting())->setTeam($team);
        $subject = (new MeetingAttendee('dsf'))->setUser($user2)->setMeeting($meeting);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canViewWhenEditOnVote()
    {
        $voter = new MeetingAttendeeVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new MeetingAttendee('dsf'))->setUser($user);

        $result = $method->invoke($voter, 'MEETING_ATTENDEE_VIEW', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @param $voter
     * @return \ReflectionMethod
     */
    private function createVoterMethodVote($voter): \ReflectionMethod
    {
        $reflection = new ReflectionObject($voter);
        $method = $reflection->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        return $method;
    }
}