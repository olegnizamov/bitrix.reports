<?php

namespace Onizamov\Reports\Classes\Crm\Activity;

use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class ActivityTable Описание таблицы для работы с сущностью Активити.
 */
class ActivityTable extends \Bitrix\Crm\ActivityTable
{

    /** @var string Активити сделки */
    public const REF_FIELD_DEAL = 'DEAL';
    public const EXP_FIELD_DEAL_ID = 'ID';
    public const CONNECTION_TO_DEAL = 'OWNER_ID';

    public static function getObjectClass()
    {
        return Activity::class;
    }

    public static function getCollectionClass()
    {
        return ActivityCollection::class;
    }

    /**
     * Добавление активити дополнительных полей.
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
                self::REF_FIELD_DEAL => new Reference(
                    self::REF_FIELD_DEAL,
                    RequestDealTable::class,
                    Join::on('this.' . self::CONNECTION_TO_DEAL, 'ref.'.self::EXP_FIELD_DEAL_ID)
                ),
            ]
        );
    }
}
