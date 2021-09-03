<?php

namespace Onizamov\Reports\Classes\Crm\Activity;

/**
 * Коллекция активити.
 */
class ActivityCollection extends \Bitrix\Crm\EO_Activity_Collection
{
    /** @var string Класс ОРМ таблицы */
    public static $dataClass = ActivityTable::class;
}
