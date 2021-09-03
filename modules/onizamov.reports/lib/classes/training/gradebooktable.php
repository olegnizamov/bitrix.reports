<?php

namespace Onizamov\Reports\Classes\Training;

use Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\User\UserTable;

/**
 * Class GradebookTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> STUDENT_ID int mandatory
 * <li> TEST_ID int mandatory
 * <li> RESULT int optional
 * <li> MAX_RESULT int optional
 * <li> ATTEMPTS int optional default 1
 * <li> COMPLETED bool ('N', 'Y') optional default 'N'
 * <li> EXTRA_ATTEMPTS int optional default 0
 * </ul>
 *
 **/
class GradebookTable extends DataManager
{

    /** @var string Пользователь */
    public const USER = 'USER';

    /** @var string Дата сдача теста */
    public const PASSING_TEST_DATE = 'PASSING_TEST_DATE';
    /** @var int ID Сделки */
    public const DEAL_ID = 'DEAL_ID';
    /** @var string индивидуальный план */
    public const REF_FIELD_DEAL = 'DEAL';


    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_gradebook';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                    'title'        => 'ID',
                ]
            ),
            new IntegerField(
                'STUDENT_ID',
                [
                    'required' => true,
                    'title'    => 'STUDENT_ID',
                ]
            ),
            new IntegerField(
                'TEST_ID',
                [
                    'required' => true,
                    'title'    => 'TEST_ID',
                ]
            ),
            new IntegerField(
                'RESULT',
                [
                    'title' => 'RESULT',
                ]
            ),
            new IntegerField(
                'MAX_RESULT',
                [
                    'title' => 'MAX_RESULT',
                ]
            ),
            new IntegerField(
                'ATTEMPTS',
                [
                    'default' => 1,
                    'title'   => 'ATTEMPTS',
                ]
            ),
            new BooleanField(
                'COMPLETED',
                [
                    'values'  => ['N', 'Y'],
                    'default' => 'N',
                    'title'   => 'COMPLETED',
                ]
            ),
            new IntegerField(
                'EXTRA_ATTEMPTS',
                [
                    'default' => 0,
                    'title'   => 'EXTRA_ATTEMPTS',
                ]
            ),
            self::USER => new Reference(
                self::USER,
                UserTable::getEntity(),
                Join::on('ref.ID', 'this.' . Gradebook::STUDENT_ID_CODE)
            ),

            self::PASSING_TEST_DATE => new ExpressionField(
                self::PASSING_TEST_DATE,
                '(SELECT DATE_END FROM ' . AttemptTable::getTableName() . '
                WHERE ' . AttemptTable::getTableName() . ' .STUDENT_ID = %s AND ' . AttemptTable::getTableName(
                ) . ' .TEST_ID = %s ORDER BY ID DESC  LIMIT 1)',
                ['STUDENT_ID', 'TEST_ID']
            ),

            self::DEAL_ID => new ExpressionField(
                self::DEAL_ID,
                '(SELECT DEAL_ID FROM ' . TrainingDealConnectionTable::getTableName() . '
                WHERE ' . TrainingDealConnectionTable::getTableName() . ' .STUDENT_ID = %s AND ' . TrainingDealConnectionTable::getTableName(
                ) . ' .TEST_ID = %s ORDER BY ID DESC  LIMIT 1)',
                ['STUDENT_ID', 'TEST_ID']
            ),

            self::REF_FIELD_DEAL => new Reference(
                self::REF_FIELD_DEAL,
                RequestDealTable::class,
                Join::on('this.' . self::DEAL_ID, 'ref.ID')
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
        return Gradebook::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return GradebookCollection::class;
    }
}