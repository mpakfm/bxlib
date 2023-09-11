<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    21:51
 */

namespace Mpakfm\Bxlib\SlimLibrary\Response;

use JsonSerializable;

class JsonData implements JsonSerializable
{
    protected int $statusCode;

    protected float $clientVersion;

    /** @var mixed */
    protected $data;

    protected ?ErrorData $error;

    public function __construct(
        int $statusCode = 200,
        $data = null,
        ?ErrorData $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->data       = $data;
        $this->error      = $error;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setVersion(float $version): void
    {
        $this->clientVersion = $version;
    }

    public function getVersion(): ?float
    {
        return $this->clientVersion;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getError(): ?ErrorData
    {
        return $this->error;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        if ($this->error !== null) {
            $data['errorMessage'] = $this->error;
        } elseif ($this->data !== null) {
            $data = $this->data;
        }
        if (is_object($data)) {
            $data->clientVersion = $this->getVersion();
        }
        if (is_array($data)) {
            $data['clientVersion'] = $this->getVersion();
        }
        return $data;
    }
}
