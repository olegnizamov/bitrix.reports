<?php

namespace Onizamov\Reports\Classes\Crm\Company;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\UserTable;
use Onizamov\Reports\Classes\Crm\UserField\FieldEnumTable;
use Bitrix\Iblock\Elements\ElementLocationsTable;

/**
 * Class CompanyTable Описание таблицы для работы с сущностью Компания.
 */
class CompanyTable extends \Bitrix\Crm\CompanyTable
{
    /** @var string Статус */
    public const STATUS = 'STATUS';
    /** @var string Локация */
    public const LOCATION = 'COMPANY_LOCATION';
    /** @var string Тип запроса */
    public const COORDINATE = 'COORDINATE';
    /** @var string Категория */
    public const CATEGORY = 'CATEGORY';
    /** @var string Регион */
    public const REGION = 'REGION';

    /**
     * Кастомный класс Обекта EO_..
     *
     * @return string
     */
    public static function getObjectClass()
    {
        return Company::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return CompanyCollection::class;
    }

    /**
     * Добавление сделку дополнительных полей.
     *
     * @return array
     * @throws ObjectPropertyException
     * @throws SystemException
     *
     * @throws ArgumentException
     */
    public static function getMap()
    {
        $map = parent::getMap();

        return array_merge(
            $map,
            [
                self::STATUS     => new Reference(
                    self::STATUS,
                    FieldEnumTable::getEntity(),
                    Join::on('ref.ID', 'this.' . Company::STATUS_CODE)
                ),
                self::REGION     => new Reference(
                    self::REGION,
                    \Bitrix\Iblock\Elements\ElementRegionsTable::getEntity(),
                    Join::on('ref.ID', 'this.' . Company::REGION_CODE)
                ),
                self::LOCATION   => new Reference(
                    self::LOCATION,
                    \Bitrix\Iblock\Elements\ElementLocationsTable::getEntity(),
                    Join::on('ref.ID', 'this.' . Company::LOCATION_CODE)
                ),
                self::COORDINATE => new Reference(
                    self::COORDINATE,
                    CompanyLocationsTable::getEntity(),
                    Join::on('ref.ID', 'this.' . Company::COMPANY_CODE)
                ),
                self::CATEGORY   => new Reference(
                    self::CATEGORY,
                    \Bitrix\Crm\StatusTable::getEntity(),
                    Join::on('ref.STATUS_ID', 'this.' . Company::CATEGORY_CODE)
                        ->where('ref.ENTITY_ID', 'COMPANY_TYPE')
                ),
            ]
        );
    }
}
