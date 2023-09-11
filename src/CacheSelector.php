<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    22:06
 */

namespace Mpakfm\Bxlib;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\SystemException;

class CacheSelector
{
    public const CACHETIME_MONTH = 2592000;
    public const CACHETIME_DAY   = 86400;
    public const CACHETIME_HOUR  = 3600;

    public const CACHE_TIME = self::CACHETIME_MONTH;

    public static function GetForm(string $formSid, $cacheTime = null): array
    {
        if ($formSid == '') {
            throw new SystemException('Form name can not be empty');
        }
        if (is_null($cacheTime)) {
            $cacheTime = self::CACHETIME_DAY;
        } else {
            $cacheTime = (int) $cacheTime;
        }

        $cacheId  = 'GetFormId-' . $formSid;
        $cacheDir = 'CacheSelector';
        $result   = null;

        $cache = Cache::createInstance();
        if ($cacheTime == 0) {
            $cache->clean($cacheId, $cacheDir);
        }
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        }

        if (!$result) {
            $cache->startDataCache();
            $con = Application::getConnection();
            $sql = "SELECT * FROM b_form WHERE SID = '{$formSid}' LIMIT 1";

            $result = $con->query($sql)->fetch();
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function getFieldAnswer(int $formId, string $sid): array
    {
        if ($formId == 0) {
            throw new SystemException('Form Id can not be 0');
        }
        if (empty($sid)) {
            throw new SystemException('Field SID can not be empty');
        }
        $sql = "SELECT a.ID as ANSWER_ID, f.* FROM b_form_field f 
INNER JOIN b_form_answer a ON a.FIELD_ID = f.ID
WHERE f.FORM_ID = '{$formId}' AND f.SID = '{$sid}'";

        $connect = Application::getConnection();
        $stmt    =  $connect->query($sql);

        $result = [];
        while ($item = $stmt->fetch()) {
            $result[] = $item;
        }
        return $result;
    }

    public static function GetUserById(int $id, $cacheTime = null)
    {
        if ($id == 0) {
            throw new SystemException('User Id can not be 0');
        }
        if (is_null($cacheTime)) {
            $cacheTime = CACHETIME_DAY;
        } else {
            $cacheTime = (int) $cacheTime;
        }

        $cacheId  = 'GetUserById' . $id;
        $cacheDir = 'CacheSelector';
        $result   = null;

        $cache = Cache::createInstance();
        if ($cacheTime == 0) {
            $cache->clean($cacheId, $cacheDir);
        }
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        }

        if (!$result) {
            $cache->startDataCache();
            $result = \CUser::GetByID($id)->Fetch();
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetBlockId(string $code, string $iblockTypeId, $lid = null, $cacheTime = null): int
    {
        if ($code == '') {
            throw new SystemException('IBlock CODE can not be empty');
        }
        if (!$lid || $lid == '') {
            $lid = 'a1';
        }
        $cacheId  = 'GetBlockId_' . $code . '_' . $iblockTypeId . '_' . $lid;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $filter = [
                'LID'            => $lid,
                'IBLOCK_TYPE_ID' => $iblockTypeId,
                'CODE'           => $code,
            ];
            $stmt = IblockTable::getRow([
                'filter' => $filter,
                'limit'  => 1,
                'cache'  => [
                    'ttl' => 0,
                ],
            ]);

            if (!$stmt) {
                $cache->abortDataCache();
                throw new \Exception('Iblock not found');
            }
            $result = $stmt['ID'];

            $cache->endDataCache($result);
        }

        return $result;
    }

    public static function GetBlockCode(int $id, $cacheTime = null): string
    {
        if ($id == 0) {
            throw new SystemException('IBlock ID can not be 0');
        }
        $cacheId  = 'GetBlockCode' . $id;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = IblockTable::getRow([
                'select' => ['CODE'],
                'filter' => [
                    'ID'           => $id,
                ],
            ])['CODE'];
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetLangById(string $id, $cacheTime = null): ?array
    {
        $id = trim($id);

        if (!$id) {
            throw new SystemException('Language ID can not be empty');
        }

        $cacheId  = 'GetLangById_' . $id;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }

        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $connect = Application::getConnection();

            $sql = "SELECT * FROM `b_lang` WHERE LID = '" . $id . "' LIMIT 1";
            $rs  = $connect->query($sql);

            $result = $rs->fetch();

            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetIblockByCode(string $code, string $iblockTypeId, $lid = null, $cacheTime = null): ?array
    {
        if ($code == '') {
            throw new SystemException('IBlock CODE can not be empty');
        }
        if (!$lid || $lid == '') {
            $lid = 's1';
        }
        $cacheId  = 'GetIblockByCode_' . $code . '_' . $iblockTypeId . '_' . $lid;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = IblockTable::getRow([
                'select' => ['*'],
                'filter' => [
                    'LID'            => $lid,
                    'IBLOCK_TYPE_ID' => $iblockTypeId,
                    'CODE'           => $code,
                ],
            ]);
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetProperty(int $iblockId, string $code, $cacheTime = null): ?array
    {
        if (!$iblockId) {
            throw new SystemException('IBlock ID can not be empty');
        }
        if ($code == '') {
            throw new SystemException('Property CODE can not be empty');
        }
        $cacheId  = 'GetProperty_' . $iblockId . $code;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }

        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = PropertyTable::getRow([
                'filter' => [
                    'IBLOCK_ID' => $iblockId,
                    'CODE'      => $code,
                ],
            ]);
            if (!$result) {
                $cache->abortDataCache();
            }

            if ($result['PROPERTY_TYPE'] == 'L') {
                $params = [
                    'filter' => [
                        'PROPERTY_ID' => $result['ID'],
                    ],
                ];
                $values = [];
                $rsList = PropertyEnumerationTable::getList($params);
                while (($item = $rsList->fetch()) != false) {
                    $values[$item['XML_ID']] = $item;
                }
                $result['VALUES'] = $values;
            }

            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetPropertyId(int $iblockId, string $code, $cacheTime = null): ?int
    {
        $prop = static::GetProperty($iblockId, $code, $cacheTime);

        $result = $prop['ID'] ?? null;

        return $result;
    }

    public static function GetStringPropertyListValues(int $propertyId, int $cacheTime = 0): array
    {
        if (!$propertyId) {
            throw new SystemException('Priperty ID can not be empty');
        }
        $result = [];

        $cacheId  = 'GetStringPropertyListValues_P' . $propertyId;
        $cacheDir = 'CacheSelector';
        if (!$cacheTime) {
            $cacheTime = static::CACHE_TIME;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $connect = Application::getConnection();

            $sql = "SELECT DISTINCT VALUE FROM `b_iblock_element_property` WHERE IBLOCK_PROPERTY_ID = " . $propertyId;
            $rs  = $connect->query($sql);
            while ($item = $rs->fetch()) {
                $result[] = $item['VALUE'];
            }
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }

        return $result;
    }

    public static function GetSectionById(int $iblockId, int $id, int $cacheTime = 0): ?array
    {
        if (!$iblockId) {
            throw new SystemException('IBlock ID can not be empty');
        }
        if (!$id) {
            throw new SystemException('Section ID can not be empty');
        }
        $cacheId  = 'GetSection-' . $iblockId . '-' . $id;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (!$cacheTime) {
            $cacheTime = static::CACHE_TIME;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = SectionTable::getRow([
                'filter' => [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE'    => 'Y',
                    'ID'        => $id,
                ],
            ]);
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function GetSection(int $iblockId, string $code, int $cacheTime = 0): ?array
    {
        if (!$iblockId) {
            throw new SystemException('IBlock ID can not be empty');
        }
        if ($code == '') {
            throw new SystemException('Section CODE can not be empty');
        }
        $cacheId  = 'GetSection' . $iblockId . $code;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (!$cacheTime) {
            $cacheTime = static::CACHE_TIME;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = SectionTable::getRow([
                'filter' => [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE'    => 'Y',
                    'CODE'      => $code,
                ],
            ]);
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function getInnerElementProperties(array $listIds, int $iblockId, $cacheTime = null): ?array
    {
        if (empty($listIds) || !$iblockId) {
            return $listIds;
        }
        $cacheId  = 'getInnerElementProperties' . implode('-', $listIds);
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = [];
            $params = [
                'filter' => [
                    "IBLOCK_ID" => $iblockId,
                ],
            ];
            $fields     = PropertyTable::getList($params)->fetchAll();
            $properties = [];
            foreach ($fields as $prop) {
                $properties[$prop['ID']] = $prop;
            }
            unset($fields);
            $order  = [];
            $filter = [
                'IBLOCK_ID' => $iblockId,
                'ID'        => $listIds,
            ];
            $select = array_merge(['ID'], ['PROPERTY_*']);
            $rs     = \CIBlockElement::GetList($order, $filter, false, false, $select);
            while ($item = $rs->Fetch()) {
                $newItem = [];
                foreach ($properties as $field) {
                    $code                    = 'PROPERTY_' . $field['ID'];
                    $newItem[$field['CODE']] = $item[$code];
                }
                $result[$item['ID']] = $newItem;
            }
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function getHLBlock(string $name, $cacheTime = null): ?int
    {
        $cacheId  = 'getHLBlock' . $name;
        $cacheDir = 'CacheSelector';
        $id       = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();

        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $id = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $sql     = "SELECT ID FROM `b_hlblock_entity` WHERE NAME = '" . $name . "'";
            $connect = Application::getConnection();
            $rs      = $connect->query($sql);
            $result  = $rs->fetch();
            $id      = $result['ID'];
            if (!$id) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($id);
        }
        return $id;
    }

    public static function getHLBlockRowByName(string $name, $cacheTime = null): ?array
    {
        $cacheId      = 'getHLBlockRowByName' . $name;
        $cacheDir     = 'CacheSelector';
        $result       = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();

        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $sql     = "SELECT * FROM `b_hlblock_entity` WHERE NAME = '" . $name . "'";
            $connect = Application::getConnection();
            $rs      = $connect->query($sql);
            $row     = $rs->fetch();
            $id      = $row['ID'];
            if (!$id) {
                $cache->abortDataCache();
            } else {
                $result = $row;
            }
            $cache->endDataCache($row);
        }
        return $result;
    }

    public static function getHLBlockById($id, $cacheTime = null): ?array
    {
        $cacheId  = 'getHLBlockById' . $id;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = HighloadBlockTable::getById($id)->fetch();
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function getHLElements($hlBlockId, array $params = [], $cacheTime = null): ?array
    {
        $cacheId  = 'getHLElements' . LANGUAGE_ID . $hlBlockId;
        $cacheDir = 'CacheSelector';
        $result   = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $hlBlock         = self::getHLBlockById($hlBlockId);
            $entity          = HighloadBlockTable::compileEntity($hlBlock);
            $ufLang          = $entity->hasField('UF_LANG');
            $entityDataClass = $entity->getDataClass();
            if (!$params) {
                $params = [
                    "select" => ["*"],
                    "order"  => ["ID" => "ASC"],
                ];
            }
            if ($ufLang) {
                $params['filter']['UF_LANG'] = LANGUAGE_ID;
            }

            $rsData = $entityDataClass::getList($params);
            $result = $rsData->fetchAll();
            if (!$result) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($result);
        }
        return $result;
    }

    public static function getUserGroup(string $stringId, $cacheTime = null): ?int
    {
        $cacheId  = 'getUserGroup' . $stringId;
        $cacheDir = 'CacheSelector';
        $id       = null;

        if (is_null($cacheTime)) {
            $cacheTime = static::CACHE_TIME;
        } else {
            $cacheTime = (int) $cacheTime;
        }
        $cache = Cache::createInstance();

        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $id = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $sql     = "SELECT ID FROM `b_group` WHERE STRING_ID = '" . $stringId . "'";
            $connect = Application::getConnection();
            $rs      = $connect->query($sql);
            $result  = $rs->fetch();
            $id      = $result['ID'];
            if (!$id) {
                $cache->abortDataCache();
            }
            $cache->endDataCache($id);
        }
        return $id;
    }
}
