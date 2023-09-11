<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    11.09.2023
 * Time:    14:13
 */

namespace Library;

use Mpakfm\Bxlib\SlimLibrary\Controller\AbstractController;
use Mpakfm\Printu;
use Psr\Http\Message\ResponseInterface;

class HttpclientController extends AbstractController
{
    /** @var bool */
    public $needVersion = false;

    protected function action(): ResponseInterface
    {
        $data = [];
        foreach ($_REQUEST as $key => $value) {
            $data[$key] = $value;
        }
        Printu::obj($data)->title('[HttpclientController] $data');
        Printu::obj($_REQUEST)->title('[HttpclientController] $_REQUEST');
        Printu::obj($_POST)->title('[HttpclientController] $_POST');
        return $this->respondWithData($data);
    }
}
