<?php

namespace Tests\Enums;

use ErrorException;
use Illuminate\Support\Carbon;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Tests\TestCase;

class PeriodicityTypeTest extends TestCase
{
    public function testDateDifferenceCountsWholePeriodsOnly()
    {
        $from = Carbon::parse('2026-06-06 12:00:00');

        $this->assertSame(0, $this->withDeprecationsAsErrors(
            fn () => PeriodicityType::getDateDifference($from, $from->copy()->addSecond(), PeriodicityType::Month),
        ));
        $this->assertSame(2, $this->withDeprecationsAsErrors(
            fn () => PeriodicityType::getDateDifference($from, $from->copy()->addMonths(2)->addDays(15), PeriodicityType::Month),
        ));
        $this->assertSame(3, $this->withDeprecationsAsErrors(
            fn () => PeriodicityType::getDateDifference($from, $from->copy()->addMonths(3), PeriodicityType::Month),
        ));
        $this->assertSame(1, $this->withDeprecationsAsErrors(
            fn () => PeriodicityType::getDateDifference($from, $from->copy()->addWeek()->addDays(6), PeriodicityType::Week),
        ));
    }

    public function testDateDifferenceKeepsTheSignWhenFromIsAfterTo()
    {
        $from = Carbon::parse('2026-06-06 12:00:00');

        $this->assertSame(-2, $this->withDeprecationsAsErrors(
            fn () => PeriodicityType::getDateDifference($from->copy()->addMonths(2)->addDays(15), $from, PeriodicityType::Month),
        ));
    }

    /**
     * Carbon 3 returns floats from diffIn*(); an implicit float-to-int conversion
     * at the int return type is a deprecation on PHP 8.1+, so it has to fail here.
     */
    private function withDeprecationsAsErrors(callable $callback): mixed
    {
        set_error_handler(function (int $errno, string $errstr): never {
            throw new ErrorException($errstr, 0, $errno);
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }
}
