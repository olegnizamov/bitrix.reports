<?php

namespace Onizamov\Reports\Classes\Training;

class Gradebook extends EO_Gradebook
{
    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = GradebookTable::class;

    public const STUDENT_ID_CODE = 'STUDENT_ID';
    public const ID_CODE = 'GRADEBOOK_ID';

    /** Результат теста - код поля*/
    public const TEST_RESULT_CODE = 'COMPLETED';
    /** Результат теста - название поля*/
    public const TEST_RESULT_NAME = 'Результат теста';

    /** Количество попыток - код поля*/
    public const ATTEMPTS_CODE = 'ATTEMPTS';
    /** Количество попыток - название поля*/
    public const ATTEMPTS_NAME = 'Количество попыток';

    /** Дата сдача теста - код поля*/
    public const PASSING_TEST_DATE_CODE = 'PASSING_TEST_DATE';
    /** Дата сдача теста - название поля*/
    public const PASSING_TEST_DATE_NAME = 'Дата сдачи теста';

}