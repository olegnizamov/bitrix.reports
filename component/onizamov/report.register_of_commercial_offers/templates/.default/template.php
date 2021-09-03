<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
/**
 * @var array $arResult
 * @var RegisterOfCommercialOffersComponent $component
 */
?>

<?php
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


