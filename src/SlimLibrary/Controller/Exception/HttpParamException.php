<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 06.02.2022
 * Time: 0:42
 */

namespace Mpakfm\Bxlib\SlimLibrary\Controller\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpParamException extends HttpSpecializedException
{
    protected $code               = 400;
    protected $message            = 'Need parametrs.';
    protected string $title       = 'required_params';
    protected string $description = 'The request requires some parametrs.';
}
