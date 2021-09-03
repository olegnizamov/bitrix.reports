<?php

namespace Onizamov\Reports\Classes\Training;

use Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Query\Join;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\User\UserTable;

/**
 * Class AttemptTable
 **/
class TrainingDealConnectionTable extends DataManager
{
    /** @var string индивидуальный план */
    public const REF_FIELD_DEAL = 'DEAL';
    /** @var int ID Сделки */
    public const DEAL_ID = 'DEAL_ID';
    /** @var int ID Обучение */
    public const GRADEBOOK_ID = 'GRADEBOOK_ID';
    /** @var int ID Курса */
    public const COURSE_ID = 'COURSE_ID';
    /** @var int ID Теста */
    public const TEST_ID = 'TEST_ID';
    /** @var string Пользователь */
    public const USER = 'USER';
    /** @var string Обучение */
    public const GRADEBOOK = 'GRADEBOOK';

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'training_deal_connection';
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
                'COURSE_ID',
                [
                    'required' => true,
                    'title'    => 'COURSE_ID',
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
                'DEAL_ID',
                [
                    'required' => true,
                    'title'    => 'DEAL_ID',
                ]
            ),
            self::REF_FIELD_DEAL => new Reference(
                self::REF_FIELD_DEAL,
                RequestDealTable::class,
                Join::on('this.' . self::DEAL_ID, 'ref.ID')
            ),
            self::USER           => new Reference(
                self::USER,
                UserTable::getEntity(),
                Join::on('ref.ID', 'this.' . self::REF_FIELD_DEAL . '.' . RequestDeal::EMPLOYEE_CODE)
            ),
            self::GRADEBOOK_ID   => new ExpressionField(
                self::GRADEBOOK_ID,
                '(SELECT ID FROM ' . GradebookTable::getTableName() . '
                WHERE ' . GradebookTable::getTableName() . ' .STUDENT_ID = %s AND ' . GradebookTable::getTableName()
                . ' .TEST_ID = %s ORDER BY ID DESC  LIMIT 1)',
                [self::USER . '.ID', 'TEST_ID']
            ),
            self::GRADEBOOK      => new Reference(
                self::GRADEBOOK,
                GradebookTable::getEntity(),
                Join::on('ref.ID', 'this.' . self::GRADEBOOK_ID)
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
        return TrainingDealConnection::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return TrainingDealConnectionCollection::class;
    }


    /**
     * Метод проверки данной записи в таблице.
     *
     * @return bool
     */
    public static function isExistRowByProperties(array $properties): bool
    {
        $result = self::getList(
            [
                'filter' => $properties,
                'select' => [
                    'ID',
                ],
            ]
        );
        if ($result->fetch()) {
            return true;
        }
        return false;
    }

    /**
     * Метод получения ID записи.
     *
     * @return mixed
     */
    public static function getFieldsByProperties(array $select, array $properties): array
    {
        $arrIDs = [];
        $result = self::getList(
            [
                'filter' => $properties,
                'select' => $select,
            ]
        );
        while ($row = $result->fetch()) {
            $arrIDs[] = $row;
        }
        return $arrIDs;
    }
}