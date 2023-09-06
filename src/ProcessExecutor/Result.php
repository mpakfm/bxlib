<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    06.09.2023
 * Time:    12:19
 */

namespace Mpakfm\Bxlib\ProcessExecutor;

class Result extends ExitCodeResult
{
    protected string $standardOutput;
    protected string $errorOutput;

    public function getStandardOutput(): string
    {
        return $this->standardOutput ?? '';
    }

    public function getErrorOutput(): string
    {
        return $this->errorOutput ?? '';
    }

    /**
     * @return string[] Без \n на концах строк
     */
    public function getStandardOutputLines(): array
    {
        if (empty($this->standardOutput)) {
            return [];
        }

        $lines = explode("\n", $this->standardOutput);
        if (end($lines) === '') {
            array_pop($lines);
        }
        return $lines;
    }

    public function setStandardOutput(string $standardOutput)
    {
        $this->standardOutput = $standardOutput;
        return $this;
    }

    public function setErrorOutput(string $errorOutput)
    {
        $this->errorOutput = $errorOutput;
        return $this;
    }

    public function fixExecutionResult()
    {
        $pattern      = [
            '|[^\PC\s]|u',
            '|\[0m|Uis',
            '|\[0\;31m|Uis',
            '|\[31m|Uis',
            '|\[32m|Uis',
            '|\[33m|Uis',
            '|\[36m|Uis',
            '|\[39m|Uis',
            '|\[41m|Uis',
            '|\[49m|Uis',
            '|\[30\;43m|Uis',
            '|\[39\;49m|Uis',
        ];
        $ErrorOutput    = preg_replace($pattern, '', $this->getErrorOutput());
        $this->setErrorOutput($ErrorOutput);

        $StandardOutput = preg_replace($pattern, '', $this->getStandardOutput());
        $this->setStandardOutput($StandardOutput);
        return $this;
    }
}
