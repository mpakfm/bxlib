<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 06.02.2022
 * Time: 20:19
 */

namespace Mpakfm\Bxlib\SlimLibrary\Controller\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpArgException extends HttpSpecializedException
{
    protected $code               = 400;
    protected $message            = 'Need arguments.';
    protected string $title       = 'required_args';
    protected string $description = 'The request requires some arguments.';
}
