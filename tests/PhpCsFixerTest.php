<?php

namespace Tests;

use Mpakfm\Bxlib\PhpCsFixer\Config;
use Mpakfm\Bxlib\PhpCsFixer\DiffLine;
use Mpakfm\Bxlib\PhpCsFixer\PhpCsFixerFilesAnalyzer;
use Mpakfm\Bxlib\ProcessExecutor\Launcher;
use Mpakfm\Bxlib\ProcessExecutor\Result;
use PhpCsFixer\Console\Command\FixCommandExitStatusCalculator;
use PHPUnit\Framework\TestCase;

class PhpCsFixerTest extends TestCase
{
    private $process;

    /**
     * Проверяет code style в php файлах.
     */
    public function testPhpFilesCodeStyle()
    {
        $this->assertNoCodeStyleProblems('.php-cs-fixer.php');
    }

    public function fixExecutionResult(Result $executionResult): Result
    {
        return $executionResult->fixExecutionResult();
    }

    /**
     * @param string $configFilename
     */
    private function assertNoCodeStyleProblems($configFilename)
    {
        $this->process = new Launcher();

        $changedFiles = $this->getChangedFiles();

        // не удалять. необходимо для корректной работы на windows при больших количествах изменных файлов
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cutto  = 20;
            $offset = 0;
            if (count($changedFiles) > $cutto) {
                //echo 'Слишком много изменененных файлов - '.count($changedFiles).'. Проход файлов будет ограничен первыми '.$cutto.'. Запустите повторно PhpCsFixerTest после фиксации изменений в выбранных '.$cutto.' файлах, для дополнительной проверки.';
                echo 'Too much changed files - ' . count($changedFiles) . '. PhpCsFixer is gonna check first ' . $cutto . '. Re-run PhpCsFixerTest after fixing and ->>> ADDING <<<- to index ' . $cutto . ' files, to encsure you have no unchecked/unfixed issues.';
                $changedFiles = array_slice($changedFiles, $offset, $cutto);
            }
        }

        // Если измененных файлов нет, то проверяем весь проект
        if (!$changedFiles) {
            $changedFiles = ['.'];
        }

        //Running on Windows PHP_OS returns Windows, WinNT, and etc
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd_line = sprintf(
                '%svendor/bin/php-cs-fixer.bat fix --config=%s --dry-run --verbose --path-mode=intersection -- %s',
                $_SERVER['DOCUMENT_ROOT'],
                escapeshellarg($configFilename),
                implode(' ', array_map('escapeshellarg', $changedFiles))
            );
            //echo  'Для отдельной проверки php-cs-fixer на windows выполните из git-bash команду: ' . $cmd_line;
            $executionResult = $this->process->exec($cmd_line);

            $executionResult = self::fixExecutionResult($executionResult);

            $message         = $this->getPhpCsFixerCommandResultMessage($executionResult, $configFilename);
            $this->assertSame(0, $executionResult->getExitCode(), $message);
        } else {
            $cmd_line = sprintf(
                'cd %s; vendor/bin/php-cs-fixer fix --config=%s --dry-run --verbose --path-mode=intersection -- %s',
                escapeshellarg($_SERVER['DOCUMENT_ROOT']),
                escapeshellarg($configFilename),
                implode(' ', array_map('escapeshellarg', $changedFiles))
            );
            $executionResult = $this->process->exec($cmd_line);

            $executionResult = self::fixExecutionResult($executionResult);

            $message         = $this->getPhpCsFixerCommandResultMessage($executionResult, $configFilename);
            $this->assertSame(0, $executionResult->getExitCode(), $message);
        }
    }

    /**
     * @return string[]
     */
    private function getChangedFiles()
    {
        // Поиск ближайшего общего предка между текущим коммитом и origin/master
        $executionResult = $this->process->exec(
            'git merge-base origin/' . GIT_BRANCH . ' HEAD'
        );
        $this->assertSame(0, $executionResult->getExitCode(), $executionResult->getErrorOutput());
        $mergeBase = $executionResult->getStandardOutputLines()[0] ?? '';
        $this->assertNotEmpty($mergeBase, 'Merge base between origin/' . GIT_BRANCH . ' and HEAD not found');

        // Получим список измененных файлов (в сделанных коммитах и рабочей директории)
        $executionResult = $this->process->exec(sprintf(
            'git diff --name-only --diff-filter=ACMRTUXB %s',
            escapeshellarg($mergeBase)
        ));
        $this->assertSame(0, $executionResult->getExitCode(), $executionResult->getErrorOutput());

        // Исключим пустые строки, обычно это последняя строка, из-за переноса
        $changedFiles = array_filter($executionResult->getStandardOutputLines(), function ($line) {
            return $line != '';
        });

        return $changedFiles;
    }

    /**
     * @param Result $consoleCommand
     * @param string $configFilename
     * @return string
     */
    private function getPhpCsFixerCommandResultMessage(Result $consoleCommand, $configFilename)
    {
        //var_dump($output);
        //var_dump($exitCode);
        //return "";
        $exitCode = $consoleCommand->getExitCode();
        $stdErr   = $consoleCommand->getErrorOutput();
        $messages = [];

        $stdOut   = $consoleCommand->getStandardOutput();
        $fixFiles = [];
        if ($stdOut != '') {
            /*  sample error/fix files output
             *  C:\OpenServer\projects\sonline.site
             * 1) local\library\Http\TestHttpAuthClient.php (braces, method_argument_space, align_multiline_comment, no_whitespace_in_blank_line)
             * 2) local\src\Handler\AjaxHandlerEx.php (braces, align_multiline_comment, no_whitespace_in_blank_line)
             * 3) tests\Api\Profile\ProfileTest.php (braces, align_multiline_comment, no_whitespace_in_blank_line)
             * 4) tests\Api\Tariffs\UserTariffsTest.php (braces, align_multiline_comment, no_whitespace_in_blank_line)
             * 5) tests\Index\IndexPageTest.php (braces, align_multiline_comment, no_whitespace_in_blank_line)
             * */
            //arrange array of files to fix
            preg_match_all('|\d+\) (.*)php \(|Uis', $stdOut, $fixFiles);
        }
        // @see https://github.com/FriendsOfPHP/PHP-CS-Fixer#exit-codes
        if ($exitCode === 1) {
            return "General error (or PHP minimal requirement not matched).\n{$stdErr}\n";
        }

        if ($exitCode & FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_FILES) {
            $messages[] = $stdErr;
        }

        if ($exitCode & FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_CHANGED_FILES) {
            $analyzer = new PhpCsFixerFilesAnalyzer($_SERVER['DOCUMENT_ROOT'] . '/' . $configFilename);

            $messages[] = 'Some files need fixing.';
            $messages[] = $this->reformatPhpCsFixerStdOut($analyzer, $consoleCommand->getStandardOutputLines());
            $messages[] = 'Run command below to automatically fix them:';
            $fixCommand = "vendor/bin/php-cs-fixer fix --config={$configFilename}";
            $messages[] = $fixCommand;

            $files = $fixFiles[1] ?? [];

            if (count($files)) {
                $messages[] = '';
                $messages[] = 'Run separate command(s) below to automatically fix selected file(s):';
                foreach ($files as $fixFile) {
                    $messages[] = $fixCommand . ' "' . $fixFile . 'php"';
                }
            }
        }

        if ($exitCode & FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_CONFIG) {
            return "Configuration error of the application.\n{$stdErr}\n";
        }

        if ($exitCode & FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG) {
            return "Configuration error of a Fixer.\n{$stdErr}\n";
        }

        if ($exitCode & FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_EXCEPTION_IN_APP) {
            return "Exception raised within the application.\n{$stdErr}\n";
        }

        if ($exitCode === 255) {
            return "PHP error.\n{$stdErr}\n";
        }

        return implode("\n", $messages) . "\n";
    }

    /**
     * @param PhpCsFixerFilesAnalyzer $analyzer
     * @param string[]                $standardOutputLines
     * @return string
     */
    private function reformatPhpCsFixerStdOut(PhpCsFixerFilesAnalyzer $analyzer, array $standardOutputLines)
    {
        $result = '';

        // Переформатируем строки с файлами.
        //
        // Было:
        // 1) /home/bitrix/www/path/to/file.php (indentation_type, array_syntax)
        //
        // Стало:
        // 1) Code must use configured indentation type (4 spaces):
        //  /home/bitrix/www/path/to/file.php:74
        // 2) PHP arrays should be declared using short syntax:
        //  /home/bitrix/www/path/to/file.php:95

        $i = 1;
        foreach ($standardOutputLines as $line) {
            if (preg_match('/^\s*\d+\)\s(.+)\s\((.+)\)/', $line, $matches)) {
                $filePath  = $matches[1];
                $ruleNames = explode(',', $matches[2]);
                $ruleNames = array_map('trim', $ruleNames);

                foreach ($ruleNames as $ruleName) {
                    $pattern      = [
                        '|[^\PC\s]|u',
                        '|\[33m|Uis',
                        '|\[39m|Uis',
                    ];
                    $ruleName     = preg_replace($pattern, '', $ruleName);
                    $errorMessage = Config::getRuleDescription($ruleName);

                    // Чтобы в консоли PhpStorm ссылка на файл была кликабельной,
                    // после пути к файлу не должно идти других символов, а впереди нужен один пробел.
                    $result .= "{$i}) {$errorMessage}:\n {$filePath}";

                    // Всего показываем не больше 10 ошибок на каждое правило в каждом файле
                    $diffGroups = $analyzer->getFileDiffForRule($filePath, $ruleName, 10);

                    // Находим строку с первой ошибкой
                    if ($diffGroups) {
                        /** @var DiffLine $diffLine */
                        foreach ($diffGroups[0] as $diffLine) {
                            if ($diffLine->isModified() && $diffLine->oldLineNumber) {
                                $result .= ":{$diffLine->oldLineNumber}";
                                break;
                            }
                        }
                    }

                    $result .= "\n";
                    $result .= PhpCsFixerFilesAnalyzer::convertDiffToString($diffGroups);

                    $i++;
                }
            } else {
                $result .= "{$line}\n";
            }
        }

        return $result;
    }
}
