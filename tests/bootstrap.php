<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    01.09.2023
 * Time:    12:43
 */

use Bitrix\Main\Loader;
use Mpakfm\Printu;
use Symfony\Component\Dotenv\Dotenv;

Printu::setPath(__DIR__ . '/../log/');

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);
define("SITE_ID", $_ENV['TEST_SITE_ID']);
define('GIT_BRANCH', 'main');

$GLOBALS["DBType"]        = 'mysql';
$_SERVER["DOCUMENT_ROOT"] = __DIR__ . '/../';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Loader::includeModule("iblock");
Loader::includeModule("sprint.migration");
