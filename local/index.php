<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    01.09.2023
 * Time:    13:52
 */

use Library\HttpclientController;
use Library\IndexController;
use Mpakfm\Bxlib\SlimLibrary\Controller\Exception\HttpVersionException;
use Mpakfm\Printu;
use Psr\Http\Message\ServerRequestInterface as RequestI;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$app = AppFactory::create();

$contentLengthMiddleware = new ContentLengthMiddleware();
$app->add($contentLengthMiddleware);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(false, true, true); //prod

// Установка обработчика ошибки Not Found
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {
        if (
            strpos($_SERVER['REQUEST_URI'], '.php') === false &&
            strpos($_SERVER['REQUEST_URI'], '.html') === false &&
            strpos($_SERVER['REQUEST_URI'], '.json') === false &&
            strpos($_SERVER['REQUEST_URI'], 'bitrix') === false &&
            strpos($_SERVER['REQUEST_URI'], 'upload') === false) {
            Printu::alert($_SERVER['REQUEST_URI'])->title('404 NOT FOUND :: REQUEST_URI ' . $_SERVER['REMOTE_ADDR']);
        }
        $response = new Response();
        $response->getBody()->write('404 NOT FOUND');
        return $response->withHeader('Content-Type', 'plain/text')->withStatus(404);
    }
);

// Установка обработчика ошибки Not Allowed
$errorMiddleware->setErrorHandler(
    HttpMethodNotAllowedException::class,
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response->getBody()->write('405 NOT ALLOWED');
        return $response->withHeader('Content-Type', 'plain/text')->withStatus(405);
    }
);

// Установка обработчика ошибки HttpVersionException
$errorMiddleware->setErrorHandler(
    HttpVersionException::class,
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {// Add error log entry
        $response = new Response();
        $json     = json_encode(['error' => true, 'text' => 'HttpVersionException (' . $exception->getCode() . ') ' . $exception->getMessage()], JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
);

// Установка обработчика ошибки Exception
$errorMiddleware->setDefaultErrorHandler(
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {
        Printu::alert($exception->getLine() . ' in file ' . $exception->getFile())->title('Code: ' . $exception->getCode() . '. Err: ' . $exception->getMessage());
        Printu::debug($exception->getLine() . ' in file ' . $exception->getFile() . "\n" . $exception->getTraceAsString())->title('[index::DefaultErrorHandler] ' . $exception->getMessage());
        $response = new Response();
        $json     = json_encode(['error' => true, 'text' => $exception->getCode() . ' ' . $exception->getMessage()], JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
);

// Установка обработчика ошибки Throwable
$errorMiddleware->setErrorHandler(
    Throwable::class,
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {
        Printu::alert($exception->getLine() . ' in file ' . $exception->getFile())->title('Code: ' . $exception->getCode() . '. Err: ' . $exception->getMessage());
        Printu::debug($exception->getLine() . ' in file ' . $exception->getFile() . "\n" . $exception->getTraceAsString())->title('[index::Throwable] ' . $exception->getMessage());
        $response = new Response();
        $json     = json_encode(['error' => true, 'text' => $exception->getCode() . ' ' . $exception->getMessage()], JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
);

// Установка обработчика ошибки ParseError
$errorMiddleware->setErrorHandler(
    ParseError::class,
    function (RequestI $request, Throwable $exception, bool $displayErrorDetails) {
        Printu::alert($exception->getLine() . ' in file ' . $exception->getFile())->title('Code: ' . $exception->getCode() . '. Err: ' . $exception->getMessage());
        Printu::debug($exception->getLine() . ' in file ' . $exception->getFile() . "\n" . $exception->getTraceAsString())->title('[index::ParseError] ' . $exception->getMessage());
        $response = new Response();
        $json     = json_encode(['error' => true, 'text' => 'ParseError :: ' . $exception->getMessage() . ' on line ' . $exception->getLine() . ' in file: ' . $exception->getFile()], JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
);

$app->map(['GET', 'POST'], '/httpclient[/]', HttpclientController::class)->setName('httpclient');
$app->get('/local[/]', IndexController::class)->setName('index');

$app->get('/', function (Request $request, Response $response, $args) use ($app) {
    $result      = new stdClass();
    $result->uri = $_SERVER['REQUEST_URI'];
    $jsonStr     = json_encode($result);

    $response->getBody()->write($jsonStr);
    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->run();
