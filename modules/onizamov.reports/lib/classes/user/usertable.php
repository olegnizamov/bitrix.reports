<?php

namespace Onizamov\Reports\Classes\User;

use Bitrix\Iblock\Elements\ElementAdaptationTable;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Onizamov\Reports\Classes\Crm\Activity\ActivityTable;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\Crm\Document\Document;
use Onizamov\Reports\Classes\Iblock\ElementPropertyTable;
use Onizamov\Reports\Classes\Tasks\TaskTable;
use Onizamov\Reports\Classes\Training\AttemptTable;

/**
 * Class UserTable
 *
 **/
class UserTable extends \Bitrix\Main\UserTable
{

    /** @var string ФИО */
    public const FULL_NAME = 'FULL_NAME';

    /** Привязка к онбордингу */
    public const ADAPTATION_PROPERTY = 'ADAPTATION_PROPERTY';

    public const ADAPTATION = 'ADAPTATION';
    public const DEAL = 'DEAL';
    public const DEAL_ID = 'DEAL_ID';
    public const TASKS = 'TASKS';
    public const COUNT_BLOG_POSTS = 'COUNT_BLOG_POSTS';

    /** Инфоблок онбординга */
    public const ADAPTATION_IBLOCK_ID = '19';

    /** Инфоблок онбординга */
    public const ADAPTATION_PROPERTY_SOTRUDNIK_ID = '93';

    /**
     * Добавление дополнительных полей.
     *
     * @return array
     */
    public static function getMap()
    {
        $map = parent::getMap();
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        return array_merge(
            $map,
            [
                self::FULL_NAME           => [
                    'data_type'  => 'string',
                    'expression' => [
                        $helper->getConcatFunction(
                            "%s",
                            "' '",
                            "%s",
                            "' '",
                            "%s"
                        ),
                        'LAST_NAME',
                        'NAME',
                        'SECOND_NAME',
                    ],
                ],
                self::ADAPTATION_PROPERTY => new Reference(
                    self::ADAPTATION_PROPERTY,
                    ElementPropertyTable::getEntity(),
                    Join::on('ref.VALUE', 'this.' . User::ID_CODE)->where(
                        'ref.IBLOCK_PROPERTY_ID',
                        self::ADAPTATION_PROPERTY_SOTRUDNIK_ID
                    )
                ),
                self::ADAPTATION          => new Reference(
                    self::ADAPTATION,
                    ElementAdaptationTable::getEntity(),
                    Join::on('ref.ID', 'this.' . self::ADAPTATION_PROPERTY . '.IBLOCK_ELEMENT_ID')
                ),

                self::DEAL_ID => new ExpressionField(
                    self::DEAL_ID,
                    '(SELECT VALUE_ID FROM b_uts_crm_deal 
                    WHERE b_uts_crm_deal.UF_CRM_1619769117 = %s ORDER BY VALUE_ID DESC  LIMIT 1)',
                    [User::ID_CODE]
                ),

                self::DEAL => new Reference(
                    self::DEAL,
                    RequestDealTable::getEntity(),
                    Join::on('ref.ID', 'this.' . self::DEAL_ID)
                ),

                self::TASKS => new \Bitrix\Main\ORM\Fields\Relations\OneToMany(
                    self::TASKS,
                    TaskTable::class,
                    TaskTable::REF_TASKS
                ),

                self::COUNT_BLOG_POSTS => new ExpressionField(
                    self::COUNT_BLOG_POSTS,
                    '(SELECT count(*) FROM ' . \Bitrix\Blog\PostTable::getTableName() . ' 
                    WHERE ' . \Bitrix\Blog\PostTable::getTableName() . '.AUTHOR_ID = %s)',
                    [User::ID_CODE]
                ),
            ]
        );
    }

    /**
     * Кастомный класс Обекта EO_..
     *
     * @return string
     */
    public static function getObjectClass()
    {
        return User::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return UserCollection::class;
    }
}