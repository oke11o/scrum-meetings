<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Meeting;
use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\MeetingVoter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class MeetingVoterTest
 * @package App\Tests\Unit\Security\Voter
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingVoterTest extends TestCase
{

    /**
     * @test
     * @dataProvider examplesSupportAttributes
     */
    public function supportAttributes($attribute, $subject, bool $expect)
    {
        $voter = new MeetingVoter();

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
                'MEETING_VIEW',
                new Meeting(),
                true,
            ],
            'edit attr support' => [
                'MEETING_EDIT',
                new Meeting(),
                true,
            ],
            'unsupport subject' => [
                'MEETING_VIEW',
                new \stdClass(),
                false,
            ],
            'unsupport attribute' => [
                'TEAM_DELETE',
                new Meeting(),
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
        $voter = new MeetingVoter();
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
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = null;
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = new Meeting();

        $result = $method->invoke($voter, '', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canNotEditOnVote()
    {
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $owner = new User();
        $team = (new Team())->setOwner($owner);
        $subject = (new Meeting())->setTeam($team);

        $result = $method->invoke($voter, 'MEETING_EDIT', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canEditOnVote()
    {
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $team = (new Team())->setOwner($user);
        $subject = (new Meeting())->setTeam($team);

        $result = $method->invoke($voter, 'MEETING_EDIT', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canViewIfCanEdit()
    {
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $team = (new Team())->setOwner($user);
        $subject = (new Meeting())->setTeam($team);

        $result = $method->invoke($voter, 'MEETING_VIEW', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canNotView()
    {
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $currentUser = new User();
        $token->getUser()->shouldBeCalled()->willReturn($currentUser);

        $owner = new User();
        $team = (new Team())->setOwner($owner)->addUser($owner);
        $subject = (new Meeting())->setTeam($team);

        $result = $method->invoke($voter, 'MEETING_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canView()
    {
        $voter = new MeetingVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $currentUser = new User();
        $token->getUser()->shouldBeCalled()->willReturn($currentUser);

        $owner = new User();
        $team = (new Team())->setOwner($owner)->addUser($owner)->addUser($currentUser);
        $subject = (new Meeting())->setTeam($team);

        $result = $method->invoke($voter, 'MEETING_VIEW', $subject, $token->reveal());

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
