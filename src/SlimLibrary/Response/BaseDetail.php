<?php
/**
 * Created by PhpStorm
 * Project: alumni
 * User:    mpak
 * Date:    17.08.2023
 * Time:    16:26
 */

namespace Mpakfm\Bxlib\SlimLibrary\Response;

use Bitrix\Iblock\InheritedProperty\ElementValues;

class BaseDetail extends JsonData
{
    public array $item;

    public function jsonSerialize(): array
    {
        $data = [];
        if ($this->error !== null) {
            $data['errorMessage'] = $this->error;
        }
        $data['item']          = $this->item;
        $data['clientVersion'] = $this->getVersion();
        return $data;
    }

    protected function setSeoData(int $iblockId, int $id): void
    {
        $ipropertyValues   = new ElementValues($iblockId, $id);
        $seo               = $ipropertyValues->getValues();
        $this->item['seo'] = [
            'metaTitle'       => $seo['ELEMENT_META_TITLE'],
            'metaKeywords'    => $seo['ELEMENT_META_KEYWORDS'],
            'metaDescription' => strip_tags(htmlspecialchars_decode($seo['ELEMENT_META_DESCRIPTION'])),
        ];
    }
}
