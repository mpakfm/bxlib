<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    01.09.2023
 * Time:    12:01
 */

namespace Mpakfm\Bxlib;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Psr7\Response;
use stdClass;
use Throwable;

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
    public $cookies;

    /** @var bool|array */
    public $allowRedirects = true;

    public function __construct(string $url, string $method, array $options = [])
    {
        $this->method  = $method;
        $this->uri     = $url;
        $this->options = $options;
    }

    public function run($type = self::RUN_TYPE_JSON_ARRAY): HttpClient
    {
        $begin        = microtime(true);
        $this->client = new Client();
        $options      = $this->options;
        $headers      = $options['headers'] ?? [];
        $headers      = array_merge($headers, $this->headers);

        if (count($headers)) {
            $options['headers'] = $headers;
        }
        if ($this->method === 'POST') {
            if (count($this->form)) {
                $options['form_params'] = $this->form;
            }
            if (count($this->multipart)) {
                $options['multipart'] = $this->multipart;
            }
            if (is_array($this->body) && count($this->body)) {
                $options['body'] = $this->body;
            } else {
                $options['body'] = $this->body;
            }
            $url = $this->uri;
        } else {
            $urlParsed = parse_url($this->uri);
            $query     = $urlParsed['query'] ?? '';
            parse_str($query, $queryParsed);
            if (count($this->form)) {
                foreach ($this->form as $key => $value) {
                    $queryParsed[$key] = $value;
                }
            }
            if (count($this->multipart)) {
                foreach ($this->multipart as $key => $value) {
                    $queryParsed[$key] = $value;
                }
            }
            if (is_array($this->body) && count($this->body)) {
                $options['body'] = $this->body;
            } else {
                $options['body'] = $this->body;
            }
            $queryNew           = http_build_query($queryParsed);
            $urlParsed['query'] = $queryNew;
            $url                = $this->buildParsedURL($urlParsed);
        }
        $options['http_errors'] = $this->httpErrors;
        if ($this->cookies) {
            $options['cookies'] = $this->cookies;
        }
        $options['allow_redirects'] = $this->allowRedirects;

        $this->response = $this->client->request($this->method, $url, $options);
        $this->code     = $this->response->getStatusCode();
        $this->reason   = $this->response->getReasonPhrase();

        $body      = $this->response->getBody();
        $this->raw = $body->getContents();

        switch ($type) {
            case self::RUN_TYPE_RAW: $this->result         = $this->raw;
                break;
            case self::RUN_TYPE_JSON_ARRAY: $this->result  = json_decode($this->raw, true);
                break;
            case self::RUN_TYPE_JSON_OBJECT: $this->result = json_decode($this->raw, false);
                break;
        }

        $end        = microtime(true);
        $this->time = $end - $begin;

        return $this;
    }

    protected function buildParsedURL(array $parts): string
    {
        $result = (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');

        return $result;
    }

    public function setForm($name, $value): HttpClient
    {
        $this->multipart   = [];
        $this->body        = null;
        $this->form[$name] = $value;
        return $this;
    }

    public function setAction($action, $kind = null): HttpClient
    {
        $this->setForm('action', $action);
        if ($kind) {
            $this->setForm('kind', $kind);
        }
        return $this;
    }

    public function setMultipart($value): HttpClient
    {
        $this->form      = [];
        $this->body      = null;
        $this->multipart = $value;
        return $this;
    }

    public function setMultipartValue($name, $value): HttpClient
    {
        $this->form             = [];
        $this->body             = null;
        $this->multipart[$name] = $value;
        return $this;
    }

    public function setBodyJson($body): HttpClient
    {
        $this->form      = [];
        $this->multipart = [];

        if ($this->method === 'POST') {
            $body = json_encode($body, JSON_THROW_ON_ERROR);

            $this->setBody($body);
        } else {
            foreach ($body as $key => $value) {
                try {
                    $json = json_encode($value, JSON_THROW_ON_ERROR);
                } catch (Exception | Throwable $e) {
                    $json = $value;
                }
                $this->form[$key] = $json;
            }
        }
        $this->setHeader('Content-Type', 'application/json');
        return $this;
    }

    public function setBody($body): HttpClient
    {
        $this->form      = [];
        $this->multipart = [];
        $this->body      = $body;
        return $this;
    }

    public function setHeader($name, $value): HttpClient
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHttpErrors(bool $value): HttpClient
    {
        $this->httpErrors = $value;

        return $this;
    }

    public function setVersion(float $version): HttpClient
    {
        return $this->setHeader('version', $version);
    }

    public function enableSession(): HttpClient
    {
        if (!$this->cookies) {
            $this->cookies = new CookieJar();
        }
        return $this;
    }

    public function disableSession(): HttpClient
    {
        if ($this->cookies) {
            unset($this->cookies);
            $this->cookies = null;
        }
        return $this;
    }

    public function enableRedirects(): HttpClient
    {
        $this->allowRedirects = true;
        return $this;
    }
}
