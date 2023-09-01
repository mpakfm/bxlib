<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    01.09.2023
 * Time:    12:45
 */

use Mpakfm\Bxlib\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testClientGetMethod()
    {
        $dtStart = microtime(true);
        $url     = $_ENV['TEST_BASE_URL'] . '/local/?a=123';
        $client  = (new HttpClient($url, 'GET', [], false));
        $client->run(HttpClient::RUN_TYPE_JSON_OBJECT);

        $dtEnd  = microtime(true);
        $dtDiff = round($dtEnd - $dtStart, 2);

        self::assertLessThanOrEqual(2, $dtDiff, 'Загрузка адреса http://bxlib.ranepa.site/local больше 2 секунд');
        self::assertSame(200, $client->response->getStatusCode(), '[testInit] :: Неверный HTTP STATUS CODE: ' . $client->response->getStatusCode());
        self::assertNotEmpty($client->result, '[testInit] :: Пустой ответ');
        self::assertTrue(($client->result->a === '123'), '[testInit] :: Неверный ответ');
    }

    public function testClientPostMethod()
    {
        $dtStart      = microtime(true);
        $url          = $_ENV['TEST_BASE_URL'] . '/local/?a=123';
        $client       = (new HttpClient($url, 'POST'));
        $client->form = [
            'name'      => 'Unit',
            'lastName'  => 'Test',
            'email'     => 'test@test.ru',
            'isBoolean' => true,
        ];
        $client->run(HttpClient::RUN_TYPE_JSON_OBJECT);
        $dtEnd  = microtime(true);
        $dtDiff = round($dtEnd - $dtStart, 2);

        self::assertLessThanOrEqual(2, $dtDiff, 'Загрузка адреса http://bxlib.ranepa.site/local больше 2 секунд');
        self::assertSame(200, $client->response->getStatusCode(), '[testInit] :: Неверный HTTP STATUS CODE: ' . $client->response->getStatusCode());
        self::assertNotEmpty($client->result, '[testInit] :: Пустой ответ');
        self::assertTrue(($client->result->a === '123'), '[testInit] :: [path::a] Неверный ответ: ' . $client->result->a);
        self::assertTrue(($client->result->name === 'Unit'), '[testInit] :: [post::name] Неверный ответ: ' . $client->result->name);
        self::assertTrue(($client->result->lastName === 'Test'), '[testInit] :: [post::lastName] Неверный ответ: ' . $client->result->lastName);
        self::assertTrue(($client->result->email === 'test@test.ru'), '[testInit] :: [post::email] Неверный ответ: ' . $client->result->email);
        self::assertTrue(($client->result->isBoolean === '1'), '[testInit] :: [post::isBoolean] Неверный ответ: ' . $client->result->isBoolean);
    }
}
