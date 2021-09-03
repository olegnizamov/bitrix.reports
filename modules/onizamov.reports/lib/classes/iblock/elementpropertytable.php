<?php

namespace Onizamov\Reports\Classes\Iblock;

use \Bitrix\Main\ORM\Data\DataManager,
    \Bitrix\Main\ORM\Fields\IntegerField,
    \Bitrix\Main\ORM\Fields\TextField,
    \Bitrix\Main\ORM\Fields\FloatField,
    \Bitrix\Main\ORM\Fields\StringField,
    \Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Loader;

/**
 * Class ElementPropertyTable
 **/
class ElementPropertyTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_iblock_element_property';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        Loader::includeModule("iblock");

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
                'IBLOCK_PROPERTY_ID',
                [
                    'required' => true,
                    'title'    => 'IBLOCK_PROPERTY_ID',
                ]
            ),
            new IntegerField(
                'IBLOCK_ELEMENT_ID',
                [
                    'required' => true,
                    'title'    => 'IBLOCK_ELEMENT_ID',
                ]
            ),
            new TextField(
                'VALUE',
                [
                    'required' => true,
                    'title'    => 'VALUE',
                ]
            ),
            new StringField(
                'VALUE_TYPE',
                [
                    'values'  => ['text', 'html'],
                    'default' => 'text',
                    'title'   => 'VALUE_TYPE',
                ]
            ),
            new IntegerField(
                'VALUE_ENUM',
                [
                    'title' => 'VALUE_ENUM',
                ]
            ),
            new FloatField(
                'VALUE_NUM',
                [
                    'title' => 'VALUE_NUM',
                ]
            ),
            new StringField(
                'DESCRIPTION',
                [
                    'validation' => [__CLASS__, 'validateDescription'],
                    'title'      => 'DESCRIPTION',
                ]
            ),
        ];
    }

    /**
     * Returns validators for DESCRIPTION field.
     *
     * @return array
     */
    public static function validateDescription()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}