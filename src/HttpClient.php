<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    01.09.2023
 * Time:    12:01
 */

namespace Mpakfm\Bxlib;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Psr7\Response;
use stdClass;

class HttpClient
{
    public const RUN_TYPE_RAW         = 'raw';
    public const RUN_TYPE_JSON_ARRAY  = 'json_array';
    public const RUN_TYPE_JSON_OBJECT = 'json_object';

    public string $method;
    public string $uri;
    public array $options = [];
    public int $code      = 0;
    public string $reason = '';
    public string $raw;

    /** @var Client */
    public Client $client;

    /** @var Response */
    public Response $response;

    /** @var array|stdClass */
    public $result = [];

    public array $headers = [];

    public array $form      = [];
    public array $multipart = [];
    public $body;

    public $httpErrors = true;

    public int $time = 0;

    /** @var CookieJarInterface */
    public CookieJarInterface $cookies;

    /** @var bool|array */
    public $allowRedirects = true;

    public function __construct(string $url, string $method, array $options = [])
    {
        $this->method  = $method;
        $this->uri     = $url;
        $this->options = $options;
    }
}
