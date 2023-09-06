<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    06.09.2023
 * Time:    13:43
 */

namespace Tests;

use Mpakfm\Bxlib\ProcessExecutor\Launcher;
use PHPUnit\Framework\TestCase;

class ProcessExecutorTest extends TestCase
{
    public function testLuancherSuccess()
    {
        $launcher = new Launcher();
        $cmdLine  = "php -v";
        $result   = $launcher->exec($cmdLine);

        self::assertSame(0, $result->getExitCode(), '[testLuancherSuccess] Ошмбка кода успешного выполнения команды (' . $cmdLine . '): ' . $result->getExitCode());
        self::assertMatchesRegularExpression('/^PHP/', $result->getStandardOutput(), 'Не соответсвует стандартный вывод: ' . $result->getStandardOutput());
        self::assertNotEmpty($result->getStandardOutputLines(), 'Не соответсвует стандартный вывод в массиве: ' . print_r($result->getStandardOutputLines(), true));
        self::assertEmpty($result->getErrorOutput(), 'Не соответсвует вывод ошибок: ' . $result->getErrorOutput());
    }

    public function testLuancherFail()
    {
        $launcher = new Launcher();
        $cmdLine  = "php -x";
        $result   = $launcher->exec($cmdLine);

        self::assertSame(1, $result->getExitCode(), '[testLuancherSuccess] Ошмбка кода неуспешного выполнения команды (' . $cmdLine . '): ' . $result->getExitCode());
        self::assertMatchesRegularExpression('/^Error/', $result->getErrorOutput(), 'Не соответсвует вывод ошибок: ' . $result->getErrorOutput());
    }
}
