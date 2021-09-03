<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
/**
 * @var array $arResult
 * @var NewClientsComponent $component
 */
//TODO корректнее переподключить на битрикс подключение JS и скрипт в JS
//TODO если компаний большое количество - возможно логичнее использовать кластеризацию
?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru-RU" type="text/javascript"></script>
<div id="map" style=" height: 400px"></div>
<script>
    ymaps.ready(init);
    var myMap;
    var arrayCoordinate = [];
    <?php foreach ($arResult['COORDINATE'] as $row): ?>
    var element = new Map();
    element.set('LONGITUDE', '<?= $row['LONGITUDE'];?>');
    element.set('LATITUDE', '<?= $row['LATITUDE'];?>');
    element.set('NAME', '<?= $row['NAME'];?>');
    arrayCoordinate.push(element);
    <?php endforeach; ?>

    function init() {
        let center_LONGITUDE = ((arrayCoordinate === 'undefined') ? arrayCoordinate[0].get('LONGITUDE') : '55.76');
        let center_LATITUDE = ((arrayCoordinate === 'undefined') ? arrayCoordinate[0].get('LATITUDE') : '37.64');

        myMap = new ymaps.Map('map', {
            center:
                [
                    center_LONGITUDE, center_LATITUDE
                ],
            zoom: 12
        }, {
            searchControlProvider: 'yandex#search'
        });

        var myCollection = new ymaps.GeoObjectCollection();
        for (let i = 0; i < arrayCoordinate.length; i++) {
            var myPlacemark = new ymaps.Placemark([
                arrayCoordinate[i].get('LONGITUDE'), arrayCoordinate[i].get('LATITUDE')
            ], {
                balloonContent: arrayCoordinate[i].get('NAME')
            }, {
                preset: 'islands#icon',
                iconColor: '#0000ff'
            });
            myCollection.add(myPlacemark);
        }
        myMap.geoObjects.add(myCollection);
        myMap.setBounds(myCollection.getBounds(), {checkZoomRange: true, zoomMargin: 3});
    }

    BX.addCustomEvent("bx.main.filter:apply", function (params) {
        myMap.destroy();
        setTimeout(() => {
            arrayCoordinate = [];
            arrIds = [];

            let container = BX.Main.gridManager.getInstanceById('new_clients').getContainer();
            let rows = BX.findChild(container, {class: "main-grid-row-body"}, true, true);
            rows.forEach(function (element) {
                if(BX.data(element,'id') !== 'template_0'){
                    arrIds.push(BX.data(element,'id'));
                }
            });

            BX.ajax.runComponentAction('onizamov:report.new_clients',
                'sendMessage', {
                    mode: 'class',
                    data: {post: arrIds},
                })
                .then(function(response) {
                    if (response.status === 'success') {
                        for (var key of arrIds) {
                            var element = response.data[`${key}`];
                            if(element !== 'undefined'){
                                var arrElement = new Map();
                                arrElement.set('LONGITUDE', element['LONGITUDE']);
                                arrElement.set('LATITUDE',element['LATITUDE']);
                                arrElement.set('NAME', element['NAME']);
                                arrayCoordinate.push(arrElement);
                            }
                        }
                        ymaps.ready(init);
                    }
                });

        }, 2000);
    });


</script>

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


