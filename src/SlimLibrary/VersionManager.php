<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    19:23
 */

namespace Mpakfm\Bxlib\SlimLibrary;

use LogicException;

class VersionManager
{
    public float $minVersion;
    public float $maxVersion;

    protected float $currentVersion;

    protected static $versions = [
        1.0, // Инициализация проекта
    ];

    public static VersionManager $obj;

    private function __construct()
    {
        $this->minVersion = static::getMinVersion();
        $this->maxVersion = static::getMaxVersion();
    }

    public static function init(): VersionManager
    {
        if (!isset(self::$obj)) {
            self::$obj = new VersionManager();
        }
        return self::$obj;
    }

    public static function getMinVersion(): float
    {
        return min(static::$versions);
    }

    public static function getMaxVersion(): float
    {
        return max(static::$versions);
    }

    public static function getVersionList(): array
    {
        return static::$versions;
    }

    public function setCurrentVersion(float $version): VersionManager
    {
        $this->currentVersion = $version;
        return $this;
    }

    public function getCurrentVersion(): float
    {
        return $this->currentVersion;
    }

    public function isVersionEqual(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion === $version);
        return $result;
    }

    public function isVersionNotEqual(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion !== $version);
        return $result;
    }

    public function isVersionInRange(float $min, float $max, bool $equal = true): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        if ($max < $min) {
            return false;
        }

        $result = ($equal)
            ? (($this->currentVersion >= $min) && ($this->currentVersion <= $max))
            : (($this->currentVersion > $min) && ($this->currentVersion < $max));

        return $result;
    }

    public function isVersionMore(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion > $version);
        return $result;
    }

    public function isVersionMoreEqual(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion >= $version);
        return $result;
    }

    public function isVersionSmall(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion < $version);
        return $result;
    }

    public function isVersionSmallEqual(float $version): bool
    {
        if (!$this->currentVersion) {
            throw new LogicException('Unknown current version');
        }
        $result = ($this->currentVersion <= $version);
        return $result;
    }
}
