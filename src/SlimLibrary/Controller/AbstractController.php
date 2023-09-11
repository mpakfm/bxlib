<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 06.02.2022
 * Time: 0:07
 */

namespace Mpakfm\Bxlib\SlimLibrary\Controller;

use Mpakfm\Bxlib\SlimLibrary\Controller\Exception\HttpArgException;
use Mpakfm\Bxlib\SlimLibrary\Controller\Exception\HttpParamException;
use Mpakfm\Bxlib\SlimLibrary\Controller\Exception\HttpVersionException;
use Mpakfm\Bxlib\SlimLibrary\Response\JsonData;
use Mpakfm\Bxlib\SlimLibrary\VersionManager;
use Mpakfm\Printu;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @OA\Info(
 *     description="Документация по REST API проекта.<br><a href=/bitrix>Bitrix</a> - панель Bitrix.",
 *     version="1.0",
 *     title="REST API"
 * )
 * @OA\Tag(
 *     name="version",
 *     description="Версии API",
 * )
 * @OA\Schema(
 *     schema="responseDefault",
 *     description="Ответ по умолчанию",
 *     type="object",
 *     title="responseDefault",
 *     @OA\Property(
 *         property="result",
 *         description="Результат",
 *         type="mixed",
 *         example="false"
 *     ),
 *     @OA\Property(
 *         property="clientVersion",
 *         description="Версия клиента",
 *         type="float",
 *         example="1.0"
 *     ),
 * )
 * @OA\Schema(
 *     schema="responseError",
 *     type="object",
 *     title="responseError",
 *     @OA\Property(
 *         property="error",
 *         description="Флаг ошибки",
 *         type="bool",
 *         example="true"
 *     ),
 *     @OA\Property(
 *         property="text",
 *         description="Сообщение",
 *         type="string",
 *         example="500 API ERROR"
 *     ),
 * )
 */

abstract class AbstractController
{
    //public static $versionController;
    /** @var Request */
    protected $request;

    /** @var ResponseInterface */
    protected $response;

    /** @var array */
    protected $args;

    /** @var array */
    protected $params;

    /** @var array */
    protected $queryParams;

    /** @var float */
    protected $version;

    /** @var bool */
    public $needVersion = true;

    public function __construct()
    {
    }

    public function prepare(Request $request, ResponseInterface $response, array $args)
    {
        $this->request     = $request;
        $this->response    = $response;
        $this->queryParams = $this->request->getQueryParams();
        $this->params      = $this->request->getParsedBody();
        $this->version     = $this->getVersionFromHeader();
        $this->args        = $args;

        $manager = VersionManager::init();
        $manager->setCurrentVersion($this->version);

        //static::$versionController = $this->version;
        // check Version
        if ($this->needVersion && !$this->version) {
            throw new HttpVersionException($this->request, "The request requires header with version number.");
        }

        $this->resolveParams();
    }

    public function __invoke(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->prepare($request, $response, $args);

        return $this->action();
    }

    protected function getVersionFromHeader()
    {
        return floatval($this->request->getHeaderLine('Version')) ?? 1.0;
    }

    public function getVersion()
    {
        return $this->version;
    }

    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpArgException($this->request, "Could not resolve argument `{$name}`.");
        }
        return $this->args[$name];
    }

    protected function resolveParams()
    {
        if (!empty($this->requiredParams)) {
            $error       = false;
            $errorParams = '';

            foreach ($this->requiredParams as $param) {
                if (!isset($this->params[$param]) || strlen($this->params[$param]) <= 0) {
                    $error = true;
                    $errorParams .= $param . ', ';
                }
            }
            if ($error) {
                throw new HttpParamException($this->request, 'Required parameters ' . substr($errorParams, 0, -2) . ' are missing or empty');
            }
        }
        return;
    }

    protected function respondWithData($data = null, int $statusCode = 200): ResponseInterface
    {
        $data = new JsonData($statusCode, $data);
        $data->setVersion($this->version);

        return $this->respond($data);
    }

    protected function respond(JsonData $data): ResponseInterface
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        if (!$json) {
            Printu::obj($data)->dt()->title('[respond] Error in json_encode. Check $data')->file('bitrix');
            throw new \Exception('Check data for json encode');
        }
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($data->getStatusCode());
    }

    protected function respondHead(JsonData $data): ResponseInterface
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        if (!$json) {
            Printu::obj('Unknown json error')->dt()->title('Error in respondHead')->file('bitrix');
            throw new \Exception('Unknown json error');
        }

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($data->getStatusCode());
    }

    abstract protected function action(): ResponseInterface;
}
