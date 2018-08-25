<?php

namespace App\Event;

use App\Entity\Meeting;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class MeetingEvent
 * @package App\Event
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingEvent extends Event
{
    /**
     * @var Meeting
     */
    private $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * @return Meeting
     */
    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }


}