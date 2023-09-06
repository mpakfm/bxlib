<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    06.09.2023
 * Time:    12:17
 */

namespace Mpakfm\Bxlib\ProcessExecutor;

class Launcher
{
    const DESCRIPTOR_STDOUT = 1;
    const DESCRIPTOR_STDERR = 2;

    public function exec(string $command, string $workingDir = null): Result
    {
        $process = proc_open(
            $command,
            [
                static::DESCRIPTOR_STDOUT => ['pipe', 'w'],
                static::DESCRIPTOR_STDERR => ['pipe', 'w'],
            ],
            $pipes,
            $workingDir
        );

        $standardOutput = stream_get_contents($pipes[static::DESCRIPTOR_STDOUT]);
        $errorOutput    = stream_get_contents($pipes[static::DESCRIPTOR_STDERR]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = proc_close($process);

        return (new Result($exitCode))
            ->setStandardOutput($standardOutput)
            ->setErrorOutput($errorOutput);
    }

    protected static function getCommandSummary($command)
    {
        $parts = explode(' ', $command, 2);
        return $parts[0]; // essentially program name
    }
}
