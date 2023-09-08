<?php

namespace Mpakfm\Bxlib\PhpCsFixer;

use LogicException;
use Mpakfm\Bxlib\ProcessExecutor\ConsoleColors;
use Mpakfm\Bxlib\ProcessExecutor\Launcher;
use PhpCsFixer\ConfigInterface;
use RuntimeException;

class FilesAnalyzer
{
    /** @var ConfigInterface */
    private $config;

    /** @var string */
    private $configPath;

    public function __construct($configPath)
    {
        $this->configPath = $configPath;
        $this->config     = include $configPath;
    }

    /**
     * @param string   $filePath
     * @param string   $ruleName
     * @param int|null $limit
     * @return array of DiffLine[]
     */
    public function getFileDiffForRule($filePath, $ruleName, $limit = null)
    {
        $rules = $this->config->getRules();
        if (!array_key_exists($ruleName, $rules)) {
            throw new LogicException("Rule '{$ruleName}' not found");
        }

        $phpCsFixerExecutable = $_SERVER['DOCUMENT_ROOT'] . '/vendor/bin/php-cs-fixer';
        $phpCsFixerConfigPath = $this->configPath;
        //$rulesConfigJson      = json_encode([$ruleName => $rules[$ruleName]]);

        $launcher = new Launcher();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $buf     = $_SERVER['DOCUMENT_ROOT'] . 'upload/' . md5($filePath) . '.out';
            $cmdLine = sprintf(
                '%s fix --config=%s --cache-file=%s --dry-run --diff -- %s >%s',
                $_SERVER['DOCUMENT_ROOT'] . 'vendor/bin/php-cs-fixer.bat',
                escapeshellarg($phpCsFixerConfigPath),
                escapeshellarg(".php_cs.cache"),
                escapeshellarg($filePath),
                $buf
            );
            $result = $launcher->exec($cmdLine);
            $result->setStandardOutput(file_get_contents($buf));
            $result->fixExecutionResult();
        } else {
            $result = $launcher->exec(sprintf(
                '%s fix --config=%s --cache-file=%s --dry-run --diff -- %s',
                escapeshellarg($phpCsFixerExecutable),
                escapeshellarg($phpCsFixerConfigPath),
                escapeshellarg(".php_cs.cache"),
                escapeshellarg($filePath)
            ));
            $result->fixExecutionResult();
        }

        if ($result->getExitCode() === 0) {
            return [];
        }

        if ($result->getExitCode() !== 8) {
            var_dump($result->getErrorOutput());
            throw new RuntimeException('Unexpected exit code: ' . $result->getExitCode());
        }

        $outputLines = $result->getStandardOutputLines();

        // Удаляем лишние строки из результата
        // Явно проверяем, что удаляем, чтобы не удалить что-то не то
        if (
            count($outputLines) >= 4
            && (mb_strpos($outputLines[1], '---------- begin diff ----------') !== false)
            && (mb_strpos($outputLines[2], '--- Original') !== false)
            && (mb_strpos($outputLines[3], '+++ New') !== false)
        ) {
            unset($outputLines[0]); // 1) /path/to/file.php (rule_name)
            unset($outputLines[1]); // ---------- begin diff ----------
            unset($outputLines[2]); // --- Original
            unset($outputLines[3]); // +++ New
        }

        $oldLineNumber  = 0;
        $newLineNumber  = 0;
        $diffLineGroups = [];
        $diffLines      = [];
        foreach ($outputLines as $line) {
            if (mb_strpos($line, 'No newline at end of file') !== false) {
                continue;
            }

            if (preg_match('/@@ -(\d+)(?:,\d+)?\s*\+(\d+)(?:,\d+)?/', $line, $matches)) {
                $oldLineNumber = ($matches[1] - 1);
                $newLineNumber = ($matches[2] - 1);
                if ($diffLines) {
                    $diffLineGroups[] = $diffLines;
                    $diffLines        = [];

                    if ($limit && count($diffLineGroups) == $limit) {
                        break;
                    }
                }
                continue;
            }

            // Всё, что идет после «end diff» не обрабатываем
            if (mb_strpos($line, '----------- end diff -----------') !== false) {
                break;
            }

            $diffLine = new DiffLine($line);

            if (!$diffLine->isInsert()) {
                $oldLineNumber++;
            }

            if (!$diffLine->isDelete()) {
                $newLineNumber++;
            }

            $diffLine->oldLineNumber = (!$diffLine->isInsert()) ? $oldLineNumber : null;
            $diffLine->newLineNumber = (!$diffLine->isDelete()) ? $newLineNumber : null;

            $diffLines[] = $diffLine;
        }

        if ($diffLines) {
            $diffLineGroups[] = $diffLines;
        }

        return $diffLineGroups;
    }

    /**
     * @param array $diffLineGroups
     * @return string
     */
    public static function convertDiffToString(array $diffLineGroups)
    {
        $result = '';

        if (empty($diffLineGroups)) {
            return $result;
        }

        $oldMaxLineNumberLength = 0;
        $newMaxLineNumberLength = 0;

        /** @var DiffLine[] $lastDiffGroup */
        $lastDiffGroup = end($diffLineGroups);
        if ($lastDiffGroup) {
            foreach ($lastDiffGroup as $diffLine) {
                $oldMaxLineNumberLength = max($oldMaxLineNumberLength, strlen((string) $diffLine->oldLineNumber));
                $newMaxLineNumberLength = max($newMaxLineNumberLength, strlen((string) $diffLine->newLineNumber));
            }
        }

        /** @var DiffLine[] $difflines */
        foreach ($diffLineGroups as $difflines) {
            $result .= "\n";
            foreach ($difflines as $diffLine) {
                if ($diffLine->isDelete()) {
                    $coloredText = ConsoleColors::red($diffLine->text);
                } elseif ($diffLine->isInsert()) {
                    $coloredText = ConsoleColors::green($diffLine->text);
                } else {
                    $coloredText = $diffLine->text;
                }

                $result .= str_pad((string) $diffLine->oldLineNumber, $oldMaxLineNumberLength + 1, ' ', STR_PAD_LEFT);
                $result .= str_pad((string) $diffLine->newLineNumber, $newMaxLineNumberLength + 1, ' ', STR_PAD_LEFT);
                $result .= ' ' . $coloredText . "\n";
            }
        }

        return $result;
    }
}
