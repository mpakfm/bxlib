<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    13:12
 */

namespace Tests;

use Mpakfm\Bxlib\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testSite()
    {
        $site = Url::getSite($_ENV['TEST_SITE_ID']);
        self::assertTrue(array_key_exists('LID', $site), '[UrlTest::testSite] поле LID отсутствует');
        self::assertSame($_ENV['TEST_SITE_ID'], $site['LID'], '[UrlTest::testSite] LID не совпадает: ' . $site['LID']);
    }
}
