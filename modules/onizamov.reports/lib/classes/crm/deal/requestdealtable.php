<?php

namespace Onizamov\Reports\Classes\Crm\Deal;

use Bitrix\Crm\DealTable;
use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\UserTable;
use Onizamov\Reports\Classes\Crm\Activity\ActivityTable;
use Onizamov\Reports\Classes\Crm\UserField\FieldEnumTable;

/**
 * Class RequestDealTable Описание таблицы для работы с сущностью сделки - Запрос.
 */
class RequestDealTable extends DealTable
{
    /** @var string Запрос передан */
    public const IS_PASSED = 'IS_PASSED';
    /** @var string Тип запроса */
    public const REQUEST_TYPE = 'REQUEST_TYPE';
    /** @var string Локация ПСП */
    public const LOCATION_PSP = 'LOCATION_PSP';
    /** @var string Локация АСП */
    public const LOCATION_ASP = 'LOCATION_ASP';
    /** @var string Общее количество м2 */
    public const TOTAL_NUMBER_OF_M_2 = 'TOTAL_NUMBER_OF_M2';
    /** @var string Дата передачи заказа в АСП */
    public const TRANSFER_DATE = 'TRANSFER_DATE';
    /** @var string Время – разница между «Дата начала» и «Дата закрытия» */
    public const PERIOD_BETWEEN = 'PERIOD_BETWEEN';
    /** @var string Ответственный менеджер новой локации */
    public const NEW_LOCATION_RESP_MANAGER = 'NEW_LOCATION_RESP_MANAGER';
    /** @var string Статус сделки */
    public const DEAL_STATUS = 'DEAL_STATUS';
    /** @var string Активити сделки */
    public const ACTIVITY = 'ACTIVITY';
    public const TYPE = 'TYPE';


    /**
     * Кастомный класс Обекта EO_..
     *
     * @return string
     */
    public static function getObjectClass()
    {
        return RequestDeal::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return RequestDealCollection::class;
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
                self::IS_PASSED                 => new Reference(
                    self::IS_PASSED,
                    FieldEnumTable::getEntity(),
                    Join::on('ref.ID', 'this.' . RequestDeal::IS_PASSED_CODE)
                ),
                self::REQUEST_TYPE              => new Reference(
                    self::REQUEST_TYPE,
                    FieldEnumTable::getEntity(),
                    Join::on('ref.ID', 'this.' . RequestDeal::REQUEST_TYPE_CODE)
                ),
                self::LOCATION_PSP              => new Reference(
                    self::LOCATION_PSP,
                    ElementLocationsTable::getEntity(),
                    Join::on('ref.ID', 'this.' . RequestDeal::LOCATION_PSP_CODE)
                ),
                self::LOCATION_ASP              => new Reference(
                    self::LOCATION_ASP,
                    ElementLocationsTable::getEntity(),
                    Join::on('ref.ID', 'this.' . RequestDeal::LOCATION_ASP_CODE)
                ),
                self::TOTAL_NUMBER_OF_M_2       => new Fields\ExpressionField(
                    self::TOTAL_NUMBER_OF_M_2,
                    '%s',
                    RequestDeal::TOTAL_NUMBER_OF_M2_CODE
                ),
                self::TRANSFER_DATE             => new Fields\ExpressionField(
                    self::TRANSFER_DATE,
                    '%s',
                    RequestDeal::TRANSFER_DATE_CODE
                ),
                self::NEW_LOCATION_RESP_MANAGER => new Reference(
                    self::NEW_LOCATION_RESP_MANAGER,
                    UserTable::getEntity(),
                    Join::on('ref.ID', 'this.' . RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE)
                ),
                self::PERIOD_BETWEEN            => new Fields\ExpressionField(
                    self::PERIOD_BETWEEN,
                    'DATEDIFF(%s, %s)',
                    [RequestDeal::CLOSE_DATE_CODE, RequestDeal::BEGIN_DATE_CODE]
                ),
                self::DEAL_STATUS => new Reference(
                    self::DEAL_STATUS,
                    \Bitrix\Crm\StatusTable::getEntity(),
                    Join::on('ref.STATUS_ID', 'this.' . RequestDeal::STAGE_ID_CODE)
                ),
                self::ACTIVITY => new \Bitrix\Main\ORM\Fields\Relations\OneToMany(
                    self::ACTIVITY,
                    \Bitrix\Tasks\Internals\TaskTable::class,
                    ActivityTable::REF_FIELD_DEAL
                ),
                self::TYPE => new Reference(
                    self::TYPE,
                    \Bitrix\Crm\StatusTable::getEntity(),
                    Join::on('ref.STATUS_ID', 'this.' . RequestDeal::STAGE_ID_CODE)
                ),
            ]
        );
    }
}
