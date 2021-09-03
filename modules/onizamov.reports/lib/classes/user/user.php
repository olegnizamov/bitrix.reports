<?php

namespace Onizamov\Reports\Classes\User;

use Bitrix\Main\EO_User;

class User extends EO_User
{
    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = UserTable::class;
    /** ID - код поля*/
    public const ID_CODE = 'ID';
    /** ID - название поля*/
    public const ID_NAME = 'ID пользователя';
    /** Подразделение - код поля*/
    public const DIVISION_CODE = 'UF_DEPARTMENT';
    /** Подразделение - название поля*/
    public const DIVISION_NAME = 'Подразделение';
    /** ФИО - код поля*/
    public const USER_ID_CODE = 'USER_ID';
    /** ФИО - код поля*/
    public const USER_ID_NAME = 'ФИО';
    /** Должность - код поля*/
    public const POST_CODE = 'WORK_POSITION';
    /** Должность - название поля*/
    public const POST_NAME = 'Должность';
    /** Дата рождения  - код поля*/
    public const BIRTHDAY_CODE = 'PERSONAL_BIRTHDAY';
    /** Дата рождения  - название поля*/
    public const BIRTHDAY_NAME = 'Дата рождения';
    /** Пол  - код поля*/
    public const GENDER_CODE = 'PERSONAL_GENDER';
    /** Пол  - название поля*/
    public const GENDER_NAME = 'Пол';
    /** Навыки - код поля*/
    public const SKILLS_CODE = 'UF_SKILLS';
    /** Навыки  - название поля*/
    public const SKILLS_NAME = 'Навыки';
    /** Интересы - код поля*/
    public const INTERESTS_CODE = 'UF_INTERESTS';
    /** Интересы - название поля*/
    public const INTERESTS_NAME = 'Интересы';
    /** Дата приема на работу  - код поля*/
    public const EMPLOYMENT_DATE_CODE = 'UF_EMPLOYMENT_DATE';
    /** Дата приема на работу  - название поля*/
    public const EMPLOYMENT_DATE_NAME = 'Дата приема на работу ';
    /** Корпоративные компетенции - код поля*/
    public const CORPORATE_COMPETENCIES_CODE = 'UF_CORPORATE_COMPETENCIES';
    /** Корпоративные компетенции  - название поля*/
    public const CORPORATE_COMPETENCIES_NAME = 'Корпоративные компетенции';
    /**  Профессиональные компетенции - код поля*/
    public const PROFESSIONAL_COMPETENCIES_CODE = 'UF_PROFESSIONAL_COMPETENCIES';
    /**  Профессиональные компетенции - название поля*/
    public const PROFESSIONAL_COMPETENCIES_NAME = 'Профессиональные компетенции';
    /**  Управленческие компетенции - код поля*/
    public const MANAGEMENT_COMPETENCIES_CODE = 'UF_MANAGEMENT_COMPETENCIES';
    /**  Управленческие компетенции - название поля*/
    public const MANAGEMENT_COMPETENCIES_NAME = 'Управленческие компетенции';
    /** Дата окончания испытательного срока  - код поля*/
    public const END_DATE_OF_PROBATIONARY_PERIOD_CODE = 'END_DATE_OF_PROBATIONARY_PERIOD';
    /**  Дата окончания испытательного срока - название поля*/
    public const END_DATE_OF_PROBATIONARY_PERIOD_NAME = 'Дата окончания испытательного срока';
    /** Результат индивидуального плана  - код поля*/
    public const RESULT_OF_INDIVIDUAL_PLAN_CODE = 'RESULT_OF_INDIVIDUAL_PLAN';
    /**  Результат индивидуального плана  - название поля*/
    public const RESULT_OF_INDIVIDUAL_PLAN_NAME = 'Результат индивидуального плана';
    /** Задача - код поля*/
    public const TASK_CODE = 'TASKS';
    /** Задача - название поля*/
    public const TASK_NAME = 'Задача';
    /** Дата начала - код поля*/
    public const START_DATE_CODE = 'TASKS_START_DATE';
    /**  Дата начала - название поля*/
    public const START_DATE_NAME = 'Дата начала';
    /** Дата выполнения  - код поля*/
    public const COMPLETION_DATE_CODE = 'TASKS_COMPLETION_DATE';
    /** Дата выполнения  - название поля*/
    public const COMPLETION_DATE_NAME = 'Дата выполнения ';
    /** Оценка задачи - код поля*/
    public const TASK_EVALUATION_CODE = 'TASKS_TASK_EVALUATION';
    /** Оценка задачи  - название поля*/
    public const TASK_EVALUATION_NAME = 'Оценка задачи';
    /**  Статус задачи - код поля*/
    public const TASK_STATUS_CODE = 'TASKS_TASK_STATUS';
    /**  Статус задачи - название поля*/
    public const TASK_STATUS_NAME = 'Статус задачи';
    /** Созданный контент - код поля*/
    public const NUMBER_OF_CREATED_CONTENT_CODE = 'NUMBER_OF_CREATED_CONTENT';
    /** Созданный контент - название поля*/
    public const NUMBER_OF_CREATED_CONTENT_NAME = 'Созданный контент';

}