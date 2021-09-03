<?php

namespace Onizamov\Reports\Classes\Crm\Document;

use Bitrix\DocumentGenerator\Model\EO_Document;

/**
 * Документы объект
 */
class Document extends EO_Document
{

    /** ID сущности */
    public const ENTITY_ID_CODE = 'VALUE';

    /** Компания - код поля*/
    public const COMPANY_CODE = 'COMPANY_ID';
    public const COMPANY_CODE_ID = '~COMPANY_ID';
    /** Компания - название поля*/
    public const COMPANY_NAME = 'Компания';

    /** Отвественный - код поля*/
    public const RESPONSIBLE_CODE = 'RESPONSIBLE';
    public const RESPONSIBLE_CODE_ID = '~RESPONSIBLE';
    /** Отвественный - название поля*/
    public const RESPONSIBLE_NAME = 'Отвественный';

    /** Запрос - код поля*/
    public const DEAL_CODE = 'DEAL_ID';
    public const DEAL_CODE_ID = '~DEAL_ID';
    /** Запрос - название поля*/
    public const DEAL_NAME = 'Запрос';

    /** ID - код поля*/
    public const ID_DOCUMENT_CODE = 'ID';
    public const ID_DOCUMENT_CODE_ID = '~ID';
    /** Номер КП - название поля*/
    public const ID_DOCUMENT_NAME = 'ID';

    /** Номер КП - код поля*/
    public const NUMBER_DOCUMENT_CODE = 'NUMBER';
    public const NUMBER_DOCUMENT_CODE_ID = '~NUMBER';
    /** Номер КП - название поля*/
    public const NUMBER_DOCUMENT_NAME = 'Номер КП';


    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = DocumentTable::class;

}
