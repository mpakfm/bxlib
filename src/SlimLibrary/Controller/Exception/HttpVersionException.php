<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 06.02.2022
 * Time: 21:01
 */

namespace Mpakfm\Bxlib\SlimLibrary\Controller\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpVersionException extends HttpSpecializedException
{
    protected $code               = 400;
    protected $message            = 'Need version.';
    protected string $title       = 'required_header';
    protected string $description = 'The request requires header with version number.';
}
