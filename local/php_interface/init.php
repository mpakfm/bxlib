<?php
/**
 * Created by PhpStorm
 * Project: test.ranepa
 * User:    mpak
 * Date:    03.08.2023
 * Time:    15:41
 */

use Mpakfm\Printu;
use Symfony\Component\Dotenv\Dotenv;

define('CACHETIME_DAY', 86400);
define('CACHETIME_MONTH', 2592000);

require_once($_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php');

Printu::setPath($_SERVER["DOCUMENT_ROOT"] . '/log/');

$dotenv = new Dotenv();
$dotenv->load($_SERVER["DOCUMENT_ROOT"] . '/.env');
