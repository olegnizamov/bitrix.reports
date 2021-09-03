<?php

namespace Onizamov\Reports\Events;

use Bitrix\Main\Config\Option;
use Onizamov\Reports\Classes\Crm\Company\Company;
use Onizamov\Reports\Classes\Crm\Company\CompanyLocationsTable;

/**
 * Событие для компании, которые участвуют в отчетах.
 */
class CompanyReportEvents
{
    /**
     * Событие обновления компании.
     *
     * @param $fieldName
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function onCrmCompanyUserFieldUpdate($fieldName)
    {
        if (!empty($fieldName[Company::ACTUAL_ADDRESS_CODE])) {
            CompanyReportEvents::createCompanyGeoPosition(
                $fieldName[Company::ACTUAL_ADDRESS_CODE],
                $fieldName['ID']
            );
        }
    }

    /**
     * Метод получения данных из Яндекса.Геопозиции и сохранения данных.
     *
     * @param $actualAddress
     * @param $companyId
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    private static function createCompanyGeoPosition($actualAddress, $companyId): void
    {
        $params = [
            'geocode' => str_replace(" ", "+", $actualAddress),   // адрес
            'format'  => 'json',
            'results' => 1,
            'apikey'  => CompanyReportEvents::getYandexGeocoderApiKey(),
        ];

        $response = json_decode(
            file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&'))
        );

        if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
            [$longitude, $latitude] = explode(
                " ",
                $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos
            );


            if (!empty(CompanyLocationsTable::getRowById($companyId))) {
                CompanyLocationsTable::update(
                    $companyId,
                    ['LATITUDE' => $longitude, 'LONGITUDE' => $latitude]
                );
            } else {
                CompanyLocationsTable::add(
                    ['ID' => $companyId, 'LATITUDE' => $longitude, 'LONGITUDE' => $latitude]
                );
            }
        }
    }


    /**
     * Метод получения Api ключа геокодера.
     *
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    private static function getYandexGeocoderApiKey(): string
    {
        return Option::get('onizamov.reports', 'geocoder_api_key');
    }

}
