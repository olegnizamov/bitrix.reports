<?php

namespace Onizamov\Reports\Classes\Vote;

use Bitrix\Vote\EO_Vote;

class Vote extends EO_Vote
{
    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = EventAnswerTable::class;
    /** Название опроса - код поля*/
    public const SURVEY_CODE = 'SURVEY';
    /** Название опроса - название поля*/
    public const SURVEY_NAME = 'Название опроса';
    /** Название вопроса - код поля*/
    public const QUESTION_CODE = 'QUESTION';
    /** Название вопроса - название поля*/
    public const QUESTION_NAME = 'Название вопроса';
    /** Дата заполнения - код поля*/
    public const FILLING_DATE_CODE = 'FILLING_DATE';
    /** Дата заполнения - название поля*/
    public const FILLING_DATE_NAME = 'Дата заполнения';
    /** Результаты - код поля*/
    public const RESULTS_CODE = 'RESULTS';
    /** Результаты - название поля*/
    public const RESULTS_NAME = 'Результаты';
    /** Количество ответивших на вопрос - код поля*/
    public const COUNT_ANSWERS_CODE = 'COUNT_ANSWERS';
    /** Количество ответивших на вопрос - название поля*/
    public const COUNT_ANSWERS_NAME = 'Количество ответивших на вопрос';
}