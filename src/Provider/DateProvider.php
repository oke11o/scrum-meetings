<?php

namespace App\Provider;

/**
 * Class DateProvider
 * @package App\Provider
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class DateProvider
{
    /**
     * @return \DateTime
     */
    public function getCurrentDate(): \DateTime
    {
        return new \DateTime();
    }
}