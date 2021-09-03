<?php

namespace Onizamov\Reports\Classes\Crm\Deal;

use Bitrix\Crm\EO_Deal_Collection;

/**
 * Class DealCollection.
 *
 */
class RequestDealCollection extends EO_Deal_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = RequestDealTable::class;
}
