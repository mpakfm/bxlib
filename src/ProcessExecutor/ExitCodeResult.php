<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    06.09.2023
 * Time:    12:18
 */

namespace Mpakfm\Bxlib\ProcessExecutor;

class ExitCodeResult
{
    protected $exitCode;

    public function __construct(int $exitCode)
    {
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
