<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\TeamVoter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class TeamVoterTest
 * @package App\Tests\Unit\Security\Voter
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class TeamVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider examplesSupportAttributes
     */
    public function supportAttributes($attribute, $subject, bool $expect)
    {
        $voter = new TeamVoter();

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
                'TEAM_VIEW',
                new Team(),
                true,
            ],
            'edit attr support' => [
                'TEAM_EDIT',
                new Team(),
                true,
            ],
            'unsupport subject' => [
                'TEAM_EDIT',
                new \stdClass(),
                false,
            ],
            'unsupport attribute' => [
                'TEAM_DELETE',
                new Team(),
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function undefinedUserOnVoteOnAttribute()
    {
        $voter = new TeamVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

//        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn(null);
        $subject = new Team();

        $result = $method->invoke($voter, '', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canViewOnVoteOnAttribute()
    {
        $voter = new TeamVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new Team())->setOwner($user);

        $result = $method->invoke($voter, 'TEAM_VIEW', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canNotViewOnVoteOnAttribute()
    {
        $voter = new TeamVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);
        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = new Team();

        $result = $method->invoke($voter, 'TEAM_VIEW', $subject, $token->reveal());

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canEditOnVoteOnAttribute()
    {
        $voter = new TeamVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = (new Team())->setOwner($user);

        $result = $method->invoke($voter, 'TEAM_EDIT', $subject, $token->reveal());

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function canNotEditOnVoteOnAttribute()
    {
        $voter = new TeamVoter();
        $method = $this->createVoterMethodVote($voter);

        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $subject = new Team();

        $result = $method->invoke($voter, 'TEAM_EDIT', $subject, $token->reveal());

        $this->assertFalse($result);
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
