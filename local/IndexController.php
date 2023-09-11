<?php

namespace Library;

use Mpakfm\Bxlib\SlimLibrary\Controller\AbstractController;
use Mpakfm\Bxlib\SlimLibrary\VersionManager;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    11.09.2023
 * Time:    11:37
 */
class IndexController extends AbstractController
{
    /** @var bool */
    public $needVersion = false;

    /**
     * @OA\Get(
     *     path="/local/",
     *     tags={"index"},
     *     @OA\Response(
     *         response="200",
     *         description="Successful index response",
     *         @OA\JsonContent(ref="#/components/schemas/responseIndex")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Error response",
     *         @OA\JsonContent(ref="#/components/schemas/responseError")
     *     ),
     * )
     * @OA\Schema(
     *     schema="responseIndex",
     *     type="object",
     *     title="responseIndex",
     *     @OA\Property(
     *         property="version",
     *         description="Текущая версия",
     *         type="float",
     *         example="1.0"
     *     ),
     *     @OA\Property(
     *         property="clientVersion",
     *         description="Версия клиента",
     *         type="float",
     *         example="1.0"
     *     ),
     * )
     */
    protected function action(): ResponseInterface
    {
        $data    = ['currentVersion' => VersionManager::getMaxVersion()];
        $manager = VersionManager::init();

        $data['isVersionEqual1'] = false;
        if ($manager->getCurrentVersion() && $manager->isVersionEqual(1)) {
            $data['isVersionEqual1'] = true;
        }

        return $this->respondWithData($data);
    }

    public function httpclient(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->prepare($request, $response, $args);
        $data = [
            'params' => $this->params,
        ];
        return $this->respond($data);
    }
}
