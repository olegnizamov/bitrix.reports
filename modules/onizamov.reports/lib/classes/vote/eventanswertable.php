<?php

namespace Onizamov\Reports\Classes\Vote;

use Bitrix\Iblock\Elements\ElementAdaptationTable;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Vote\EventQuestionTable;
use Onizamov\Reports\Classes\Crm\Activity\ActivityTable;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\Crm\Document\Document;
use Onizamov\Reports\Classes\Iblock\ElementPropertyTable;
use Onizamov\Reports\Classes\Tasks\TaskTable;
use Onizamov\Reports\Classes\Training\AttemptTable;

/**
 * Class EventAnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_QUESTION_ID int,
 * <li> ANSWER_ID int,
 * <li> MESSAGE text,
 * </ul>
 *
 */
class EventAnswerTable extends \Bitrix\Main\Entity\DataManager
{
    public const EVENT_QUESTION = 'EVENT_QUESTION';
    public const QUESTION = 'QUESTION';
    public const ANSWER = 'ANSWER';
    public const ID = 'ID';
    public const EVENT_QUESTION_ID = 'EVENT_QUESTION_ID';
    public const ANSWER_ID = 'ANSWER_ID';
    public const MESSAGE = 'MESSAGE';
    public const EVENT = 'EVENT';
    public const VOTE_USER = 'VOTE_USER';
    public const USER = 'USER';
    public const COUNT_ANSWERS = 'COUNT_ANSWERS';
    public const VOTE = 'VOTE';

    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_vote_event_answer';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            self::ID                => [
                'data_type'    => 'integer',
                'primary'      => true,
                'autocomplete' => true,
            ],
            self::EVENT_QUESTION_ID => [
                'data_type' => 'integer',
            ],
            self::ANSWER_ID         => [
                'data_type' => 'integer',
            ],
            self::MESSAGE           => [
                'data_type' => 'text',
            ],
            self::EVENT_QUESTION    => [
                'data_type' => '\Bitrix\Vote\EventQuestionTable',
                'reference' => [
                    '=this.EVENT_QUESTION_ID' => 'ref.ID',
                ],
            ],
            self::EVENT             => [
                'data_type' => '\Bitrix\Vote\EventTable',
                'reference' => [
                    "=this." . self::EVENT_QUESTION . ".EVENT_ID" => 'ref.ID',
                ],
            ],
            self::VOTE_USER         => [
                'data_type' => '\Bitrix\Vote\UserTable',
                'reference' => [
                    "=this." . self::EVENT . ".VOTE_USER_ID" => 'ref.ID',
                ],
            ],
            self::USER              => [
                'data_type' => '\Onizamov\Reports\Classes\User\UserTable',
                'reference' => [
                    "=this." . self::VOTE_USER . ".AUTH_USER_ID" => 'ref.ID',
                ],
            ],
            self::ANSWER            => [
                'data_type' => '\Bitrix\Vote\AnswerTable',
                'reference' => [
                    '=this.ANSWER_ID' => 'ref.ID',
                ],
            ],
            self::QUESTION          => [
                'data_type' => '\Bitrix\Vote\QuestionTable',
                'reference' => [
                    "=this." . self::EVENT_QUESTION . ".QUESTION_ID" => 'ref.ID',
                ],
            ],
            self::VOTE              => [
                'data_type' => '\Bitrix\Vote\ChannelTable',
                'reference' => [
                    "=this." . self::QUESTION . ".VOTE_ID" => 'ref.ID',
                ],
            ],
            self::COUNT_ANSWERS     => new ExpressionField(
                self::COUNT_ANSWERS,
                '(SELECT count(*) FROM ' . EventQuestionTable::getTableName() . '
                WHERE ' . EventQuestionTable::getTableName() . ' .QUESTION_ID = %s )',
                [self::EVENT_QUESTION . ".QUESTION_ID"]
            ),
        ];
    }

    /**
     * Кастомный класс Обекта EO_..
     *
     * @return string
     */
    public static function getObjectClass()
    {
        return Vote::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return VoteCollection::class;
    }
}