<?php

namespace Onizamov\Reports\Classes\Crm\Company;

use Bitrix\Crm\EO_Company_Collection;

/**
 * Class CompanyCollection.
 *
 */
class CompanyCollection extends EO_Company_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = CompanyTable::class;
}
