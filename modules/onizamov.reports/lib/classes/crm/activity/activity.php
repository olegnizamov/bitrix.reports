<?php

namespace Onizamov\Reports\Classes\Crm\Activity;

/**
 * Активити.
 */
class Activity extends \Bitrix\Crm\EO_Activity
{
    /** Встреча ? - код поля*/
    public const IS_MEETING = 'MEETING';
    /** Звонок ? - код поля*/
    public const IS_CALL = 'CALL';
    /** Email ? - код поля*/
    public const IS_EMAIL = 'EMAIL';

    /** @var string Класс ОРМ таблицы */
    public static $dataClass = ActivityTable::class;
}
