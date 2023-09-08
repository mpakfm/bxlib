<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 25.10.2021
 * Time: 14:55
 */

namespace Mpakfm\Bxlib;

class TypeCaster
{
    public static function bool($value): ?bool
    {
        if ($value === null) {
            return null;
        }
        return (bool) $value;
    }

    public static function boolStrong($value): bool
    {
        if ($value === null) {
            return false;
        }
        return (bool) $value;
    }

    public static function int($value): ?int
    {
        if ($value === null) {
            return null;
        }
        return (int) $value;
    }

    public static function intStrong($value): int
    {
        if ($value === null) {
            return 0;
        }
        return (int) $value;
    }

    public static function string($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        return $value;
    }

    public static function id($value): ?int
    {
        if (empty($value)) {
            return null;
        }
        return static::int($value);
    }

    /**
     * Ожидаемый формат то, что распознает strtotime
     * Возвращает Y-m-d
     * @param mixed $value
     */
    public static function date($value): ?string
    {
        if (empty($value) || !strtotime($value) || strpos($value, '0000-00-00') !== false) {
            return null;
        }
        return date('Y-m-d', strtotime($value));
    }

    /**
     * Ожидаемый формат то, что распознает strtotime
     * Возвращает ISO 8601 date: "2004-02-12T15:19:21+00:00"
     * @param mixed $value
     */
    public static function datetime($value): ?string
    {
        if (empty($value) || strpos($value, '0000-00-00') !== false) {
            return null;
        }
        // Если на входе число, считаем его как timestamp и его не надо преобразовывать
        if (is_int($value)) {
            return date('c', $value);
        }
        $value = strtotime($value);
        if ($value === false) {
            return null;
        }
        return date('c', $value);
    }

    public static function absoluteUrl($url): ?string
    {
        $url = static::string($url);
        if ($url === null) {
            return null;
        }

        Url::ensureValidAbsoluteUrl($url);
        return $url;
    }
}
