<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
/**
 * @var array $arResult
 * @var QuestionnairesAndSurveysComponent $component
 */
?>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru-RU" type="text/javascript"></script>
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

<button id='voting-diagram-create' class="ui-btn ui-btn-primary">Сформировать диаграмму</button>
<script>
    BX.bind(BX('voting-diagram-create'), 'click', function () {
        BX.cleanNode(BX('voting'));
        BX.ajax.runComponentAction('onizamov:report.questionnaires_surveys',
            'getDiagrams', {
                mode: 'class',
            })
            .then(function (response) {
                var object = BX.create(
                    {
                        tag: 'div',
                        html: response.data.HTML,
                    });
                BX.append(object, BX('voting'));
            });
    })
    BX.addCustomEvent("bx.main.filter:apply", function (params) {
        BX.cleanNode(BX('voting'));
    });
</script>

<div id="voting">
</div>
