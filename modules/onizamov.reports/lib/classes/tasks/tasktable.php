<?php

/**
 * Class TasksTable
 *
 * @package Bitrix\Tasks
 **/

namespace Onizamov\Reports\Classes\Tasks;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Localization\Loc;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Tasks\Internals\Task\MemberTable;
use Bitrix\Tasks\Util\Entity\DateTimeField;
use Bitrix\Tasks\Util\UserField;
use Onizamov\Reports\Classes\Crm\Activity\ActivityTable;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\Iblock\ElementPropertyTable;
use Onizamov\Reports\Classes\User\User;
use Onizamov\Reports\Classes\User\UserTable;

Loc::loadMessages(__FILE__);

class TaskTable extends \Bitrix\Tasks\Internals\TaskTable
{
    /** Привязка к онбордингу */
    public const REF_TASKS = 'REF_TASKS';
    /** Задача - название */
    public const TASK_TITLE = 'TITLE';
    /** Задача - дата начала */
    public const TASK_DATE_START = 'DATE_START';
    /** Задача - дата выполнения */
    public const TASK_COMPLETION_DATE = 'CLOSED_DATE';
    /** Задача - оценка */
    public const TASK_MARK = 'MARK';
    /** Задача - статус */
    public const TASK_STATUS = 'STATUS';

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        $map = parent::getMap();

        return array_merge(
            $map,
            [
                self::REF_TASKS => new Reference(
                    self::REF_TASKS,
                    UserTable::class,
                    Join::on('this.RESPONSIBLE_ID', 'ref.' . User::ID_CODE)->whereNot('this.ZOMBIE', 'Y')
                ),
            ]
        );
    }

}