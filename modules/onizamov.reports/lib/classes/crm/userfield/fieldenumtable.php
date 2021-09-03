<?php
namespace Onizamov\Reports\Classes\Crm\UserField;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class FieldEnumTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_FIELD_ID int optional
 * <li> VALUE string(255) mandatory
 * <li> DEF bool ('N', 'Y') optional default 'N'
 * <li> SORT int optional default 500
 * <li> XML_ID string(255) mandatory
 * </ul>
 *
 * @package Bitrix\User
 **/

class FieldEnumTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_user_field_enum';
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
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            new IntegerField(
                'USER_FIELD_ID',
                [
                ]
            ),
            new StringField(
                'VALUE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateValue'],
                ]
            ),
            new BooleanField(
                'DEF',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                ]
            ),
            new StringField(
                'XML_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateXmlId'],
                ]
            ),
        ];
    }

    /**
     * Returns validators for VALUE field.
     *
     * @return array
     */
    public static function validateValue()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for XML_ID field.
     *
     * @return array
     */
    public static function validateXmlId()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}
