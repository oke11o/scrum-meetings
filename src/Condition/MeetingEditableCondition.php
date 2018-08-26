<?php

namespace App\Condition;

use App\Entity\Meeting;
use App\Provider\DateProvider;

/**
 * Class MeetingEditableCondition
 * @package App\Condition
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingEditableCondition
{
    /**
     * @var DateProvider
     */
    private $dateProvider;

    public function __construct(DateProvider $dateProvider)
    {
        $this->dateProvider = $dateProvider;
    }

    /**
     * @param Meeting $meeting
     * @return bool
     */
    public function availableEdit(Meeting $meeting):bool
    {
        $currentDate = $this->dateProvider->getCurrentDate();
        $currentDay = new \DateTime($currentDate->format('Y-m-d 00:00:00'));
        if ($meeting->getCreatedAt() < $currentDay) {
            return false;
        }

        return !$meeting->getIsClosed();
    }
}