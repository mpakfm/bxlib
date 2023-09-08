<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    12:07
 */

namespace Mpakfm\Bxlib;

use CSite;
use Exception;

class Url
{
    protected static $sitesCache = [];

    public static function detectProtocol()
    {
        $isApacheHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
        $isNginxHttps  = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');

        $https  = ($isApacheHttps || $isNginxHttps) ? true : false;
        $result = ($https) ? 'https' : 'http';

        return $result;
    }

    public static function getSite($id, $force = false)
    {
        $result = null;

        if (!array_key_exists($id, static::$sitesCache) || $force === true) {
            $rsSites = CSite::GetByID($id);
            $arSite  = $rsSites->Fetch();

            if (!empty($arSite)) {
                static::$sitesCache[$id] = $arSite;
                $result                  = $arSite;
            }
        } else {
            $result = static::$sitesCache[$id];
        }

        return $result;
    }

    public static function getBaseUrl(?string $site = SITE_ID): string
    {
        $arSite = static::getSite($site);
        if (!empty($arSite)) {
            $serverName = $arSite['SERVER_NAME'] ?? null;
            $serverName = trim($serverName);
        }
        if (!$serverName) {
            $serverName = $_SERVER['SERVER_NAME'];
        }
        $protocol = static::detectProtocol();
        $result   = $protocol . '://' . $serverName . '/';
        return $result;
    }

    public static function getFullLink($relative, $site = SITE_ID): ?string
    {
        $relative = is_string($relative) ? trim($relative) : null;
        if (!$relative) {
            return null;
        }
        $dir             = '';
        $frontendBaseUrl = self::getBaseUrl();
        $arSite          = self::getSite($site);
        if (!empty($arSite)) {
            $dir = ltrim(($arSite['DIR'] ?? '/'), '/');
            if ($dir) {
                while (mb_strpos($relative, $dir) === 1) {
                    $relative = str_replace($dir, '', $relative);
                }
            }
        }
        $result = $frontendBaseUrl . $dir . ltrim($relative, '/');
        return $result;
    }

    public static function ensureValidAbsoluteUrl(string $url, $site = SITE_ID): void
    {
        $frontendBaseUrl = self::getBaseUrl($site);
        if (mb_strpos($url, $frontendBaseUrl) !== 0) {
            throw new Exception(
                "Unexpected URL: '{$url}'. Only absolute frontend URLs are allowed: {$frontendBaseUrl}example."
                . " Use URL class to get a correct URL."
            );
        }
    }
}
