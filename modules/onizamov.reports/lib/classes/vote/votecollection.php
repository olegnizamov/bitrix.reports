<?php

namespace Onizamov\Reports\Classes\Vote;

use Bitrix\Vote\EO_Vote_Collection;

class VoteCollection extends EO_Vote_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = EventAnswerTable::class;
}