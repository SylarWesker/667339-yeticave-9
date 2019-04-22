<?php

namespace SylarWesker\Yeticave\Test;

//require_once  dirname(__FILE__) .  '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once sprintf(
            "%s%s..%sutils%sutils.php",
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }

    // Тестирование is_equal_or_less_hour.
    /**
     * @dataProvider timeIntervalProvider
     */
    public function test_is_equal_or_less_hour($interval, $expected)
    {
        $this->assertEquals(is_equal_or_less_hour($interval), $expected);
    }

    public function timeIntervalProvider()
    {
        return [
            '1 hour' => [new \DateInterval('P0Y0M0DT1H0M0S'), true],
            '1 year and one hour' => [new \DateInterval('P1YT1H0M0S'), false],
            '1 hour and 1 second' => [new \DateInterval('PT1H0M1S'), false],
            '59 min 59 sec' => [new \DateInterval('P0Y0M0DT0H59M59S'), true],
            '30 min' => [new \DateInterval('P0Y0M0DT0H30M0S'), true],
            '30 sec' => [new \DateInterval('P0Y0M0DT0H0M30S'), true],
            '1 year and 59 min 59 sec' => [new \DateInterval('P1Y0M0DT0H59M59S'), false],
            'zero interval' => [new \DateInterval('P0Y0M0DT0H0M0S'), true]
        ];
    }
}