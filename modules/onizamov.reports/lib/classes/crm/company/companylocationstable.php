<?php

namespace Onizamov\Reports\Classes\Crm\Company;

use Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField;

/**
 * Class CompanyLocationsTable Описание таблицы для работы с сущностью Координаты компании - широта, долгота.
 */
class CompanyLocationsTable extends \Bitrix\Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'onizamov_company_locations';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                ]
            ),
            new StringField(
                'LATITUDE',
                [
                    'required' => true,
                ]
            ),
            new StringField(
                'LONGITUDE',
                [
                    'required' => true,
                ]
            ),
        ];
    }
}
