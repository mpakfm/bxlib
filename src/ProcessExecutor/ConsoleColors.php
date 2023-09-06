<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    06.09.2023
 * Time:    12:36
 */

namespace Mpakfm\Bxlib\ProcessExecutor;

class ConsoleColors
{
    public static function red(string $text): string
    {
        return self::color($text, "\e[0;31m", "\e[0m");
    }

    public static function green(string $text): string
    {
        return self::color($text, "\e[0;32m", "\e[0m");
    }

    private static function color(string $text, string $colorBegin, string $colorEnd)
    {
        return $colorBegin . $text . $colorEnd;
    }
}
