<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 10.02.2022
 * Time: 23:02
 */

namespace Mpakfm\Bxlib\SlimLibrary\Response;

class BaseList extends JsonData
{
    public int $count;
    public array $items;
    public array $page;

    public function jsonSerialize(): array
    {
        $data = [];
        if ($this->error !== null) {
            $data['errorMessage'] = $this->error;
        }
        $data = [
            'items' => $this->items,
            'count' => $this->count,
        ];
        if (!is_null($this->page)) {
            $data['page'] = $this->page;
        }
        $data['clientVersion'] = $this->getVersion();
        return $data;
    }
}
