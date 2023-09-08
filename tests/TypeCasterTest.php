<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    15:25
 */

namespace Tests;

use Mpakfm\Bxlib\TypeCaster;
use PHPUnit\Framework\TestCase;

class TypeCasterTest extends TestCase
{
    public function testIntCaster()
    {
        $var    = 3;
        $result = TypeCaster::int($var);
        self::assertSame(3, $result, '[TypeCasterTest::testIntCaster] int->int error');

        $var    = 3.0;
        $result = TypeCaster::int($var);
        self::assertSame(3, $result, '[TypeCasterTest::testIntCaster] float->int (3.0) error');

        $var    = 3.7;
        $result = TypeCaster::int($var);
        self::assertSame(3, $result, '[TypeCasterTest::testIntCaster] float->int (3.7) error');

        $var    = '3';
        $result = TypeCaster::int($var);
        self::assertSame(3, $result, '[TypeCasterTest::testIntCaster] string->int error');

        $var    = 'asd';
        $result = TypeCaster::int($var);
        self::assertSame(0, $result, '[TypeCasterTest::testIntCaster] string->int (0) error');

        $var    = true;
        $result = TypeCaster::int($var);
        self::assertSame(1, $result, '[TypeCasterTest::testIntCaster] bool->int (true) error');

        $var    = false;
        $result = TypeCaster::int($var);
        self::assertSame(0, $result, '[TypeCasterTest::testIntCaster] bool->int (false) error');

        $var    = null;
        $result = TypeCaster::int($var);
        self::assertNull($result, '[TypeCasterTest::testIntCaster] string->int (null) error');
    }

    public function testIntStrongCaster()
    {
        $var    = '5700';
        $result = TypeCaster::intStrong($var);
        self::assertSame(5700, $result, '[TypeCasterTest::testIntStrongCaster] string->int error');

        $var    = 'asd';
        $result = TypeCaster::intStrong($var);
        self::assertSame(0, $result, '[TypeCasterTest::testIntStrongCaster] string->int (0) error');

        $var    = null;
        $result = TypeCaster::intStrong($var);
        self::assertSame(0, $result, '[TypeCasterTest::testIntStrongCaster] null->int error');
    }

    public function testIdCaster()
    {
        $var    = '5700';
        $result = TypeCaster::id($var);
        self::assertSame(5700, $result, '[TypeCasterTest::testIdCaster] string->id error');

        $var    = '0';
        $result = TypeCaster::id($var);
        self::assertNull($result, '[TypeCasterTest::testIdCaster] string-zero->id error');
    }

    public function testStringCaster()
    {
        $var    = 3;
        $result = TypeCaster::string($var);
        self::assertSame('3', $result, '[TypeCasterTest::testIntStrongCaster] int->string error');

        $var    = 3.0;
        $result = TypeCaster::string($var);
        self::assertSame('3', $result, '[TypeCasterTest::testIntStrongCaster] float->string (3.0) error');

        $var    = 3.8;
        $result = TypeCaster::string($var);
        self::assertSame('3.8', $result, '[TypeCasterTest::testIntStrongCaster] float->string (3.8) error');

        $var    = true;
        $result = TypeCaster::string($var);
        self::assertSame('1', $result, '[TypeCasterTest::testIntStrongCaster] bool->string (true) error');

        $var    = false;
        $result = TypeCaster::string($var);
        self::assertNull($result, '[TypeCasterTest::testIntStrongCaster] bool->string (false) error');

        $var    = null;
        $result = TypeCaster::string($var);
        self::assertNull($result, '[TypeCasterTest::testIntStrongCaster] null->string error');
    }

    public function testBoolCaster()
    {
        $var    = 1;
        $result = TypeCaster::bool($var);
        self::assertTrue($result, '[TypeCasterTest::testIntStrongCaster] int->bool error');

        $var    = 0;
        $result = TypeCaster::bool($var);
        self::assertFalse($result, '[TypeCasterTest::testIntStrongCaster] zero->bool error');

        $var    = 'qwerty';
        $result = TypeCaster::bool($var);
        self::assertTrue($result, '[TypeCasterTest::testIntStrongCaster] string->bool (qwerty) error');

        $var    = '';
        $result = TypeCaster::bool($var);
        self::assertFalse($result, '[TypeCasterTest::testIntStrongCaster] string->bool (empty) error');

        $var    = true;
        $result = TypeCaster::bool($var);
        self::assertTrue($result, '[TypeCasterTest::testIntStrongCaster] bool->bool (true) error');

        $var    = false;
        $result = TypeCaster::bool($var);
        self::assertFalse($result, '[TypeCasterTest::testIntStrongCaster] bool->bool (false) error');

        $var    = null;
        $result = TypeCaster::bool($var);
        self::assertNull($result, '[TypeCasterTest::testIntStrongCaster] null->string error');
    }

    public function testBoolStrongCaster()
    {
        $var    = 1;
        $result = TypeCaster::boolStrong($var);
        self::assertTrue($result, '[TypeCasterTest::testBoolStrongCaster] int->bool error');

        $var    = 0;
        $result = TypeCaster::boolStrong($var);
        self::assertFalse($result, '[TypeCasterTest::testBoolStrongCaster] zero->bool error');

        $var    = null;
        $result = TypeCaster::boolStrong($var);
        self::assertFalse($result, '[TypeCasterTest::testBoolStrongCaster] null->string error');
    }

    public function testDateCaster()
    {
        $var    = '12.08.2022';
        $result = TypeCaster::date($var);
        self::assertSame('2022-08-12', $result, '[TypeCasterTest::testDateCaster] 12.08.2022 error');

        $var    = '12.08.2022 12:00';
        $result = TypeCaster::date($var);
        self::assertSame('2022-08-12', $result, '[TypeCasterTest::testDateCaster] 12.08.2022 12:00 error');
    }

    public function testDateTimeCaster()
    {
        $var    = '12.08.2022';
        $result = TypeCaster::datetime($var);
        self::assertSame('2022-08-12T00:00:00+03:00', $result, '[TypeCasterTest::testDateTimeCaster] 12.08.2022 error');

        $var    = '12.08.2022 12:35';
        $result = TypeCaster::datetime($var);
        self::assertSame('2022-08-12T12:35:00+03:00', $result, '[TypeCasterTest::testDateTimeCaster] 12.08.2022 12:00 error');
    }
}
