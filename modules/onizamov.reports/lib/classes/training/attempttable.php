<?php

namespace Onizamov\Reports\Classes\Training;

use Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class AttemptTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TEST_ID int mandatory
 * <li> STUDENT_ID int mandatory
 * <li> DATE_START datetime mandatory
 * <li> DATE_END datetime optional
 * <li> STATUS string(1) optional default 'B'
 * <li> COMPLETED bool ('N', 'Y') optional default 'N'
 * <li> SCORE int optional default 0
 * <li> MAX_SCORE int optional default 0
 * <li> QUESTIONS int optional default 0
 * </ul>
 *
 **/
class AttemptTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_attempt';
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
                'TEST_ID',
                [
                    'required' => true,
                    'title'    => 'TEST_ID',
                ]
            ),
            new IntegerField(
                'STUDENT_ID',
                [
                    'required' => true,
                    'title'    => 'STUDENT_ID',
                ]
            ),
            new DatetimeField(
                'DATE_START',
                [
                    'required' => true,
                    'title'    => 'DATE_START',
                ]
            ),
            new DatetimeField(
                'DATE_END',
                [
                    'title' => 'DATE_END',
                ]
            ),
            new StringField(
                'STATUS',
                [
                    'default'    => 'B',
                    'validation' => [__CLASS__, 'validateStatus'],
                    'title'      => 'STATUS',
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
                'SCORE',
                [
                    'default' => 0,
                    'title'   => 'SCORE',
                ]
            ),
            new IntegerField(
                'MAX_SCORE',
                [
                    'default' => 0,
                    'title'   => 'MAX_SCORE',
                ]
            ),
            new IntegerField(
                'QUESTIONS',
                [
                    'default' => 0,
                    'title'   => 'QUESTIONS',
                ]
            ),
        ];
    }

    /**
     * Returns validators for STATUS field.
     *
     * @return array
     */
    public static function validateStatus()
    {
        return [
            new LengthValidator(null, 1),
        ];
    }
}