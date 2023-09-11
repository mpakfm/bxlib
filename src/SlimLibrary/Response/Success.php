<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    21:55
 */

namespace Mpakfm\Bxlib\SlimLibrary\Response;

use Mpakfm\Bxlib\TypeCaster;

class Success extends JsonData
{
    public bool $result;

    public function __construct($data = null, ?ErrorData $error = null)
    {
        parent::__construct(200, $data, $error);
    }

    public function jsonSerialize(): array
    {
        $data = [];
        if ($this->error !== null) {
            $data['errorMessage'] = $this->error;
        }
        $data = [
            'result' => TypeCaster::boolStrong($this->result),
        ];
        $data['resultId'] = null;
        if (property_exists($this, 'resultId')) {
            $data['resultId'] = TypeCaster::int($this->resultId);
        }
        if ($this->data) {
            $data['data'] = $this->data;
        }
        $data['clientVersion'] = $this->getVersion();
        return $data;
    }
}
