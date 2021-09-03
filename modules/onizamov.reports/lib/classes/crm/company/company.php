<?php

namespace Onizamov\Reports\Classes\Crm\Company;

use Bitrix\Crm\EO_Company;

/**
 * Компания.
 */
class Company extends EO_Company
{
    /** Статус - код поля*/
    public const STATUS_ID = 146;
    /** Статус - код поля*/
    public const STATUS_CODE = 'UF_CRM_1618307460509';
    /** Статус - название поля*/
    public const STATUS_NAME = 'Статус';

    /** Категория - код поля*/
    public const CATEGORY_CODE = 'COMPANY_TYPE';
    /** Категория - название поля*/
    public const CATEGORY_NAME = 'Категория';

    /** Локация - код поля*/
    public const LOCATION_CODE = 'UF_CRM_1618315911';
    /** Локация - название поля*/
    public const LOCATION_NAME = 'Локация';

    /** Фактический адрес - код поля*/
    public const ACTUAL_ADDRESS_CODE = 'UF_CRM_1618318045';
    /** Фактический адрес - название поля*/
    public const ACTUAL_ADDRESS_NAME = 'Фактический адрес';

    /** Регион - код поля*/
    public const REGION_CODE = 'UF_CRM_1617974053';

    /** Дата создания - код поля*/
    public const DATE_OF_CREATION_CODE = 'DATE_CREATE';
    /** Дата создания - название поля*/
    public const DATE_OF_CREATION_NAME = 'Дата создания';

    /** Компания - код поля */
    public const COMPANY_CODE = 'ID';
    /** Компания - название поля */
    public const COMPANY_NAME = 'Компания';
    /** Компания - тип сущности */
    public const COMPANY_ENTITY_ID = 'COMPANY_TYPE';


    /** Компания - код поля */
    public const COMPANY_TITLE = 'TITLE';

    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = CompanyTable::class;
}
