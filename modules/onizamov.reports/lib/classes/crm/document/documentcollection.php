<?php

namespace Onizamov\Reports\Classes\Crm\Document;

use \Bitrix\DocumentGenerator\Model\EO_Document_Collection;

/**
 * Class DocumentCollection.
 *
 */
class DocumentCollection extends EO_Document_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = DocumentTable::class;
}
