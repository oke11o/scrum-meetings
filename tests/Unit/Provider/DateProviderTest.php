<?php

namespace App\Tests\Unit\Provider;

use App\Provider\DateProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class DateProviderTest
 * @package App\Tests\Unit\Provider
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class DateProviderTest extends TestCase
{

    /**
     * @test
     */
    public function getCurrentDate()
    {
        $provider = new DateProvider();
        $date = $provider->getCurrentDate();
        $this->assertInstanceOf(\DateTime::class, $date);

        $cur = new \DateTime();
        $min = new \DateTime('-30 sec');

        $this->assertLessThan($cur, $date);
        $this->assertGreaterThan($min, $date);
    }
}
