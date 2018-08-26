<?php

namespace App\Tests\Unit\Condition;

use App\Condition\MeetingEditableCondition;
use App\Entity\Meeting;
use App\Provider\DateProvider;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class MeetingEditableConditionTest
 * @package App\Tests\Unit\Condition
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingEditableConditionTest extends TestCase
{
    private const CURRENT_DATE = '2018-08-26 08:13:32';
    /**
     * @var MeetingEditableCondition
     */
    private $condition;
    /**
     * @var DateProvider|ObjectProphecy
     */
    private $dateProvider;

    public function setUp()
    {
        $this->dateProvider = $this->prophesize(DateProvider::class);
        $this->condition = new MeetingEditableCondition($this->dateProvider->reveal());

        $this->dateProvider->getCurrentDate()->willReturn(new DateTime(self::CURRENT_DATE));
    }

    /**
     * @test
     * @dataProvider examples
     */
    public function available($meetingDate, $isClose, $expect)
    {
        $meeting = $this->createMeeting($meetingDate, $isClose);

        $this->assertEquals($expect, $this->condition->availableEdit($meeting));
    }

    public function examples()
    {
        return [
            [
                'meetingDate' => new DateTime('2018-08-25 08:13:32'),
                'isClose' => false,
                'expect' => false,
            ],
            [
                'meetingDate' => new DateTime('2018-08-26 01:13:32'),
                'isClose' => false,
                'expect' => true,
            ],
            [
                'meetingDate' => new DateTime('2018-08-26 01:13:32'),
                'isClose' => true,
                'expect' => false,
            ],
        ];
    }

    /**
     * @param $meetingDate
     * @param $isClose
     * @return Meeting
     */
    private function createMeeting($meetingDate, $isClose): Meeting
    {
        return (new Meeting())->setCreatedAt($meetingDate)->setIsClosed($isClose);
    }
}
