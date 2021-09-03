<?php

namespace Onizamov\Reports\Classes\Crm\Deal;

use Bitrix\Crm\EO_Deal;

/**
 * Сделка типа - Запросы.
 */
class RequestDeal extends EO_Deal
{
    /** ID категории запроса*/
    public const CATEGORY_ID_REQUEST = 2;

    /** ID категории запроса*/
    public const DEAL_STAGE = 'DEAL_STAGE_2';
    public const DEAL_STAGE_FINAL = 'F';

    /** Локация Инфоблок*/
    public const LOCATION_IBLOCK_ID = 21;

    /** Локация ПСП - код поля*/
    public const LOCATION_PSP_CODE = 'UF_CRM_1618571513';
    /** Локация ПСП - название поля*/
    public const LOCATION_PSP_NAME = 'Локация ПСП';

    /** Ответственный менеджер - код поля*/
    public const RESP_MANAGER_CODE = 'ASSIGNED_BY';
    /** Ответственный менеджер - название поля*/
    public const RESP_MANAGER_NAME = 'Ответственный менеджер';
    /** Ответственный менеджер ID- название поля*/
    public const ASSIGNED_BY_ID_CODE = 'ASSIGNED_BY_ID';
    /** Ответственный менеджер ID- название поля*/
    public const COMPANY_ID = 'COMPANY_ID';
    /** Компания - код поля*/
    public const COMPANY_CODE = 'COMPANY';
    /** Компания - название поля*/
    public const COMPANY_NAME = 'Компания';

    /** Общее количество м2 - код поля*/
    public const TOTAL_NUMBER_OF_M2_CODE = 'UF_CRM_1617974436186';
    /** Общее количество м2 - название поля*/
    public const TOTAL_NUMBER_OF_M2_NAME = 'Общее количество м2';

    /** Дата передачи заказа в АСП - код поля*/
    public const TRANSFER_DATE_CODE = 'UF_CRM_1619510742564';
    /** Дата передачи заказа в АСП - название поля*/
    public const TRANSFER_DATE_NAME = 'Дата передачи заказа в АСП';

    /** Локация АСП - код поля*/
    public const LOCATION_ASP_CODE = 'UF_CRM_1618571530';
    /** Локация АСП - название поля*/
    public const LOCATION_ASP_NAME = 'Локация АСП';

    /** Ответственный менеджер новой локации - код поля*/
    public const NEW_LOCATION_RESP_MANAGER_CODE = 'UF_CRM_1618571611';
    /** Ответственный менеджер новой локации - название поля*/
    public const NEW_LOCATION_RESP_MANAGER_NAME = 'Ответственный менеджер новой локации';

    /** Дата запуска в производство - код поля*/
    public const LAUNCH_DATE_CODE = 'CLOSEDATE';
    /** Дата запуска в производство - название поля*/
    public const LAUNCH_DATE_NAME = 'Дата запуска в производство';

    /** Примечание - код поля*/
    public const COMMENTS_CODE = 'COMMENTS';
    /** Примечание - название поля*/
    public const COMMENTS_NAME = 'Примечание';

    /** Запрос передан - код поля*/
    public const IS_PASSED_CODE = 'UF_CRM_1618572017847';

    /** Запрос - код поля*/
    public const REQUEST_ID_CODE = 'ID';
    /** Запрос - название поля*/
    public const REQUEST_ID_NAME = 'Запрос';

    /** Запрос - код поля*/
    public const REQUEST_TYPE_ID = 251;
    /** Запрос - код поля*/
    public const REQUEST_TYPE_CODE = 'UF_CRM_1619182526860';
    /** Запрос - название поля*/
    public const REQUEST_TYPE_NAME = 'Тип запроса';

    /** Дата запуска в производство - код поля*/
    public const BEGIN_DATE_CODE = 'BEGINDATE';
    /** Дата запуска в производство - название поля*/
    public const BEGIN_DATE_NAME = 'Дата начала';

    /** Дата запуска в производство - код поля*/
    public const CLOSE_DATE_CODE = 'CLOSEDATE';
    /** Дата запуска в производство - название поля*/
    public const CLOSE_DATE_NAME = 'Дата завершения';

    /** Дата запуска в производство - код поля*/
    public const PEDIOD_CODE = 'PERIOD_BETWEEN';
    /** Дата запуска в производство - название поля*/
    public const PEDIOD_NAME = 'Время';

    /** Название заявки - код поля*/
    public const REQUEST_TITLE = 'TITLE';

    /** Менеджер - код поля*/
    public const MANAGER_CODE = 'ASSIGNED_BY';
    /** Менеджер - название поля*/
    public const MANAGER_NAME = 'Менеджер';

    /** Количество звонков - код поля*/
    public const NUMBER_OF_CALLS_CODE = 'NUMBER_OF_CALLS';
    /** Количество звонков - название поля*/
    public const NUMBER_OF_CALLS_NAME = 'Количество звонков';

    /** Количество звонков - код поля*/
    public const NUMBER_OF_MEETINGS_CODE = 'NUMBER_OF_MEETINGS';
    /** Количество звонков - название поля*/
    public const NUMBER_OF_MEETINGS_NAME = 'Количество встреч';

    /** Количество звонков - код поля*/
    public const NUMBER_OF_EMAILS_CODE = 'NUMBER_OF_EMAILS';
    /** Количество звонков - название поля*/
    public const NUMBER_OF_EMAILS_NAME = 'Количество писем';

    /** Причина неуспешного запроса - код поля*/
    public const REASON_OF_FAILED_REQUEST_CODE = 'REASON_OF_FAILED_REQUEST';
    /** Причина неуспешного запроса - название поля*/
    public const REASON_OF_FAILED_REQUEST_NAME = 'Причина неуспешного запроса';

    /** Количество запросов всего - код поля*/
    public const NUMBER_OF_DEALS_CODE = 'NUMBER_OF_DEALS';
    /** Количество запросов всего - название поля*/
    public const NUMBER_OF_DEALS_NAME = 'Количество запросов всего';

    /** Успешные запросы - код поля*/
    public const NUMBER_OF_SUCCESS_DEALS_CODE = 'NUMBER_OF_SUCCESS_DEALS';
    /** Успешные запросы- название поля*/
    public const NUMBER_OF_SUCCESS_DEALS_NAME = 'Успешные запросы';

    /** Неуспешные запросы - код поля*/
    public const NUMBER_OF_FAILED_DEALS_CODE = 'NUMBER_OF_FAILED_DEALS';
    /** Неуспешные запросы - название поля*/
    public const NUMBER_OF_FAILED_DEALS_NAME = 'Неуспешные запросы';

    /** Статус сделки семантический ID - код поля*/
    public const STAGE_SEMANTIC_ID_CODE = 'STAGE_SEMANTIC_ID';
    /** Статус сделки XML_ID систему - код поля*/
    public const STAGE_ID_CODE = 'STAGE_ID';

    /** Успешный запрос ? - код поля*/
    public const IS_SUCCESS_DEAL_CODE = 'IS_SUCCESS_DEAL';
    /** Неуспешный запрос ? - код поля*/
    public const IS_FAILED_DEAL_CODE = 'IS_FAILED_DEAL';

    /** Сумма и валюта - код поля*/
    public const OPPORTUNITY_CODE = 'OPPORTUNITY';
    /** Сумма и валюта - код поля*/
    public const OPPORTUNITY_NAME = 'Сумма и валюта';

    /** Тип запроса - код поля*/
    public const TYPE_ID_CODE = 'TYPE_ID';
    /** Тип запроса - код поля*/
    public const TYPE_ID_NAME = 'Тип запроса';

    /** Запрос - код поля*/
    public const REQUEST_DEAL_ID_CODE = 'UF_CRM_1620897335';
    /** Запрос - название поля*/
    public const REQUEST_DEAL_ID_NAME = 'Заявка';

    /** Номер КП - код поля*/
    public const NUMBER_COMMERCIAL_PROPOSAL_CODE = 'UF_CRM_1619524393188';

    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = RequestDealTable::class;


    /** Название обучения  - код поля*/
    public const TRAINING_NAME_CODE = 'TITLE';
    /** Название обучения  - название поля*/
    public const TRAINING_NAME_NAME = 'Название обучения';

    /** Дата начала обучения  - код поля*/
    public const DATE_START_TRAINING_CODE = 'UF_CRM_1619768866';
    /** Дата начала обучения  - название поля*/
    public const DATE_START_TRAINING_NAME = 'Дата начала обучения';

    /** Программа обучения  - код поля*/
    public const PROGRAMM_TRAINING_CODE = 'UF_CRM_1624460195114';
    /** Программа обучения  - название поля*/
    public const PROGRAMM_TRAINING_NAME = 'Программа обучения';

    /** Результат - код поля*/
    public const RESULT_CODE = 'UF_CRM_1619187121287';
    /** Результат  - название поля*/
    public const RESULT_NAME = 'Результат';

    /** Сотрудник - код поля*/
    public const EMPLOYEE_CODE = 'UF_CRM_1619769117';
    /** Сотрудник - название поля*/
    public const EMPLOYEE_NAME = 'Сотрудник';


}
