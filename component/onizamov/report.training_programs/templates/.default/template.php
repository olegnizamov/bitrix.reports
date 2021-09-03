<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
/**
 * @var array $arResult
 * @var TrainingProgramsComponent $component
 */

$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.grid',
    'titleflex',
    [
        'GRID_ID'             => $arResult['GRID_ID'],
        'HEADERS'             => $arResult['HEADERS'],
        'FILTER'              => $arResult['FILTERS'],
        'SORT'                => $arResult['SORT'],
        'SORT_VARS'           => $arResult['SORT_VARS'],
        'ROWS'                => $arResult["ROWS"],
        'IS_EXTERNAL_FILTER'  => false,
        'TOTAL_ROWS_COUNT'    => $arResult['PAGINATION']['TOTAL'],
        "EDITABLE"            => true,
        'AJAX_OPTION_JUMP'    => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_LOADER'         => null,
        'FILTER_PRESETS'      => [
            'default' => [
                'name'   => 'Фильтр',
                'fields' => $arResult['FILTERS'],
            ],
        ],
        'ACTION_PANEL'        => [
            'GROUPS' => [
                [
                    'ITEMS' => $arResult['ACTION_PANEL_GROUPS_ITEMS'],
                ],
            ],
        ],
        'PAGINATION'          => $arResult['PAGINATION'],

    ],
    $component
);
?>

<style>
    #popup-window-content-bx-selector-dialog-UF_DEPARTMENT .bx-finder-company-department-children-opened a[href^="#DR"] .bx-finder-company-department-check-inner {
        display: none;
    }
    #popup-window-content-bx-selector-dialog-UF_DEPARTMENT .bx-finder-company-department-children-opened a[href^="#DR"]{
        padding-top:0px;
    }
</style>
