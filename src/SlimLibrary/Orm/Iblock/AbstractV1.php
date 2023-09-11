<?php
/**
 * Created by PhpStorm
 * Project: bxlib
 * User:    mpak
 * Date:    08.09.2023
 * Time:    22:02
 */

namespace Mpakfm\Bxlib\SlimLibrary\Orm\Iblock;

use Bitrix\Main\Loader;
use CIBlockElement;
use CIBlockSection;
use LogicException;
use Mpakfm\Bxlib\CacheSelector;

/**
 * Class AbstractV1
 * Библиотека по работе с Инфоблоком версии v1 через старый ORM: \CIBlockElement::GetList
 * @package Library\Orm\Iblock
 * @see: https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/getlist.php
 */
abstract class AbstractV1
{
    public const DEFAULT_SORT       = 'SORT, CREATED';
    public const DEFAULT_SORT_ORDER = 'desc, desc';
    public const DEFAULT_OFFSET     = 0;
    public const DEFAULT_LIMIT      = 20;

    protected static string $iblockType;
    protected static string $iblockCode;

    protected int $iblockId;

    protected array $sort;
    protected array $filter;

    public int $limit;
    public int $numPage;
    public int $offset;

    protected $standartSortByFields = [
        'ID'                 => 'id',
        'SORT'               => 'sort',
        'TIMESTAMP_X'        => 'timestamp_x',
        'NAME'               => 'name',
        'ACTIVE_FROM'        => 'active_from',
        'DATE_ACTIVE_FROM'   => 'date_active_from',
        'ACTIVE_TO'          => 'active_to',
        'DATE_ACTIVE_TO'     => 'date_active_to',
        'WF_STATUS_ID'       => 'status',
        'CODE'               => 'code',
        'IBLOCK_ID'          => 'iblock_id',
        'MODIFIED_BY'        => 'modified_by',
        'ACTIVE'             => 'active',
        'SHOW_COUNTER'       => 'show_counter',
        'SHOW_COUNTER_START' => 'show_counter_start',
        'SHOWS'              => 'shows',
        'RAND'               => 'rand',
        'XML_ID'             => 'xml_id',
        'EXTERNAL_ID'        => 'external_id',
        'TAGS'               => 'tags',
        'CREATED'            => 'created',
        'CREATED_DATE'       => 'created_date',
        'CNT'                => 'cnt',
    ];

    /** Получить тип ИБ */
    public static function getIblockType(): string
    {
        return static::$iblockType;
    }

    /** Получить код ИБ */
    public static function getIblockCode(): string
    {
        return static::$iblockCode;
    }
    /**
     * AbstractV1 constructor.
     * @todo: $this->filter['ACTIVE_FROM'] && $this->filter['ACTIVE_TO']
     */
    public function __construct(bool $isSetDefaultFilter = true)
    {
        if (!static::$iblockType || !static::$iblockCode) {
            throw new \Exception('Unknown IBLOCK_TYPE/IBLOCK_CODE');
        }

        Loader::includeModule('iblock');

        $this->iblockId            = CacheSelector::GetBlockId(static::$iblockCode, static::$iblockType, SITE_ID);
        $this->filter['IBLOCK_ID'] = $this->iblockId;
        $this->setDefault();

        if ($isSetDefaultFilter) {
            $this->filter['ACTIVE'] = 'Y';
            $this->filter[]         = [
                'LOGIC'              => 'OR',
                'DATE_ACTIVE_FROM'   => false,
                '<=DATE_ACTIVE_FROM' => ConvertTimeStamp(false, "FULL"),
            ];
            $this->filter[]         = [
                'LOGIC'            => 'OR',
                'DATE_ACTIVE_TO'   => false,
                '>=DATE_ACTIVE_TO' => ConvertTimeStamp(false, "FULL"),
            ];
        }
    }

    public function setDefault(): void
    {
        $this->setOrder(static::DEFAULT_SORT, static::DEFAULT_SORT_ORDER);
        $this->limit  = static::DEFAULT_LIMIT;
        $this->offset = static::DEFAULT_OFFSET;
    }

    public function setOrder(string $fields, string $orders): void
    {
        $this->sort = [];
        $sortFields = explode(',', $fields);
        $sortOrder  = explode(',', $orders);
        foreach ($sortFields as $key => $field) {
            $field = trim($field);
            if (array_key_exists($field, $this->standartSortByFields)) {
                $by = $this->standartSortByFields[$field];
            } else {
                $by = 'property_' . $field;
            }
            if (!isset($sortOrder[$key])) {
                $order = 'desc';
            } else {
                $order = (trim(strtolower($sortOrder[$key])) == 'asc' ? 'asc' : 'desc');
            }
            $this->sort[$by] = $order;
        }
    }

    /** Метод обновления счетчика показа элементов на 1 */
    public function addShowCounter(int $id, $currentCounter = null): void
    {
        $el  = new CIBlockElement();
        $cnt = ($currentCounter ? $currentCounter : 0) + 1;
        $res = $el->Update($id, ['SHOW_COUNTER' => $cnt]);
        if (!$res) {
            throw new LogicException('Warning: don`t updated addShowCounter [IBLOCK_ID::' . static::$iblockCode . '] for id: ' . $id . ', $currentCounter: ' . $currentCounter);
        }
    }

    /** Метод получения одного элемента по полю CODE Возвращает результат по стандарту метода getList */
    public function getItem(string $code): ?array
    {
        $this->setFilter('code', $code);
        return $this->getList(true);
    }

    protected function filterById(string $value)
    {
        $values = explode(',', $value);
        if (count($values) > 1) {
            $trimValues = [];
            foreach ($values as $item) {
                $id = trim((int) $item);
                if ($id) {
                    $trimValues[] = $id;
                }
            }
            $this->filter['ID'] = $trimValues;
        } else {
            $this->filter['ID'] = $value;
        }
    }

    protected function filterByCode(string $value)
    {
        $values = explode(',', $value);
        if (count($values) > 1) {
            $trimValues = [];
            foreach ($values as $item) {
                $item = trim($item);
                if ($item != '') {
                    $trimValues[] = $item;
                }
            }
            $this->filter['CODE'] = $trimValues;
        } else {
            $this->filter['CODE'] = $value;
        }
    }

    protected function filterBySection(string $value)
    {
        $values = explode(',', $value);
        if (count($values) > 1) {
            $trimValues = [];
            foreach ($values as $item) {
                $item = trim($item);
                if ($item != '') {
                    $trimValues[] = $item;
                }
            }
            $code = $trimValues;
        } else {
            $code = $value;
        }
        $stmt = CIBlockSection::GetList([], ['IBLOCK_ID' => $this->iblockId, 'CODE' => $code], true, ['ID', 'CODE']);
        while ($section = $stmt->Fetch()) {
            $this->filter['IBLOCK_SECTION_ID'][] = $section['ID'];
        }
    }

    public function setNavParams(): array
    {
        $navParams = [];
        if ($this->limit == 0) {
            $navParams['bShowAll']  = true;
            $navParams['nPageSize'] = 100000;
        } else {
            $navParams['nPageSize'] = $this->limit;
        }
        if ($this->numPage) {
            $navParams['iNumPage'] = $this->numPage;
        } elseif ($this->offset) {
            $navParams['iNumPage'] = ceil($this->offset / $navParams['nPageSize']);
        } else {
            $navParams['iNumPage'] = 1;
        }
        return $navParams;
    }

    /** Метод добавления фильтров, каждое поле доступное для фильтрации описывается в методе отдельно в отличие от сортировки. */
    abstract public function setFilter(string $field, $value): void;

    /** Метод получения списка элементов */
    abstract public function getList(): array;
}
