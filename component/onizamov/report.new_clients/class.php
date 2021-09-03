<?php

namespace Onizamov\Components;

use Bitrix\Crm\EO_Company;
use Bitrix\Crm\StatusTable;
use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Company\Company;
use Onizamov\Reports\Classes\Crm\Company\CompanyLocationsTable;
use Onizamov\Reports\Classes\Crm\Company\CompanyTable;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class NewClientsComponent - компонент о новых клиентах.
 */
class NewClientsComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'new_clients';
    /** @var array Поля */
    public $arFields = [];
    /** @var array Фильтр */
    public $arFilter = [];
    /** @var object Объект навигации */
    private $pageObj;
    /** @var int текущее смещение */
    private $currentOffset;
    /** @var int Количество страниц */
    private $nPageSize;
    /** @var int Общее клоичество сделкок запросов */
    private $totalCountOfCompanyCollection;
    /** @var array Массив координатов */
    private $arrCoordinates;

    /**
     * Метод выполнения компонента.
     *
     * @return mixed|void|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        if (Loader::includeModule("crm") && Loader::includeModule("onizamov.reports")) {
            $this->setHeadersAndPanels();
            $this->getData();
            $this->includeComponentTemplate();
        }
    }

    /**
     * Метод форматирования Заголовков таблицы и панели редактирования.
     *
     * Отчет должен иметь фильтр по следующим полям:
     * - Локация – по выбранным локациям из списка;
     * - Ответственный менеджер – по выбранным пользователям из списка;
     * - Компания – по выбранным компаниям, которые учувствуют в сделках;
     * - Дата передачи заказа – по указанной дате, либо за период времени.
     */
    public function setHeadersAndPanels()
    {
        $arrLocations = $this->getLocations();
        $arrStatuses = $this->getStatuses();
        $arrCompanyTypes = $this->getCompanyTypes();

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['HEADERS'] = [
            [
                'id'     => Company::COMPANY_CODE,
                'name'   => Company::COMPANY_NAME,
                'sort'   => false,
                'filter' => false,
            ],
            [
                'id'     => Company::CATEGORY_CODE,
                'name'   => Company::CATEGORY_NAME,
                'sort'   => Company::CATEGORY_CODE,
                'type'   => 'list',
                'filter' => true,
                'items'  => $arrCompanyTypes,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => Company::STATUS_CODE,
                'name'   => Company::STATUS_NAME,
                'sort'   => Company::STATUS_CODE,
                'type'   => 'list',
                'filter' => true,
                'items'  => $arrStatuses,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => Company::LOCATION_CODE,
                'name'   => Company::LOCATION_NAME,
                'sort'   => Company::LOCATION_CODE,
                'filter' => true,
                'type'   => 'list',
                'items'  => $arrLocations,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => Company::ACTUAL_ADDRESS_CODE,
                'name'   => Company::ACTUAL_ADDRESS_NAME,
                'sort'   => false,
                'filter' => false,
            ],
            [
                'id'     => Company::DATE_OF_CREATION_CODE,
                'name'   => Company::DATE_OF_CREATION_NAME,
                'sort'   => Company::DATE_OF_CREATION_CODE,
                'filter' => false,
            ],
        ];

        $this->arResult['FILTERS'] = $this->arResult['HEADERS'];
        foreach ($this->arResult['FILTERS'] as $key => &$filter) {
            if (!$filter['filter']) {
                unset($this->arResult['FILTERS'][$key]);
            }
        }

        $snippet = new Snippet();
        $this->arResult['ACTION_PANEL_GROUPS_ITEMS'] = [
            $snippet->getEditButton(),
            $snippet->getForAllCheckbox(),
        ];
    }

    /**
     * Метод получения данных.
     * Условия выборки – поле «Запрос передан» = «Заполнено».
     * В отчет должны попадать запросы только с заполненными значениями полей «Дата передачи запроса».
     *
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getData()
    {
        $companyPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setSortFromUI($companyPrepareCollection);
        $this->setFilterOptionFromUI($companyPrepareCollection);
        $this->setPaginationSettings();
        $companyPrepareCollection->countTotal(true);
        $companyPrepareCollection->setOffset($this->currentOffset);
        $companyPrepareCollection->setLimit($this->nPageSize);
        $this->totalCountOfCompanyCollection = $companyPrepareCollection->exec()->getCount();
        $companyCollection = $companyPrepareCollection->fetchCollection();
        foreach ($companyCollection as $companyObj) {
            //Хак - если Категория пустая. Механизм фильтрации ORM почему-то не работает.
            if (empty($companyObj->get(CompanyTable::CATEGORY))) {
                continue;
            }

            $this->arResult['ROWS'][] = [
                'id'      => $companyObj->getId(),
                'columns' => $this->getPreparedFields($companyObj),
            ];
        }
        $this->arResult['COORDINATE'] = $this->arrCoordinates;
        $this->setPagination();
    }

    /**
     * Метод получения локаций.
     *
     * @return array
     */
    private function getLocations(): array
    {
        $result = [];
        $locationCollection = ElementLocationsTable::query()
            ->setSelect(
                ['ID', 'NAME', 'ACTIVE']
            )
            ->where('IBLOCK_ID', RequestDeal::LOCATION_IBLOCK_ID)
            ->fetchCollection();

        foreach ($locationCollection as $locationObj) {
            $result[$locationObj->getId()] = $locationObj->getName();
        }
        return $result;
    }

    /**
     * Метод получения статусов.
     *
     * @return array
     */
    private function getStatuses(): array
    {
        $result = [];
        $fieldDb = \CUserFieldEnum::GetList([], ['USER_FIELD_ID' => Company::STATUS_ID]);
        while ($field = $fieldDb->Fetch()) {
            $result[$field['ID']] = $field['VALUE'];
        }
        return $result;
    }

    /**
     * Метод получения категорий(типов компании).
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getCompanyTypes(): array
    {
        $result = [];

        $list = StatusTable::getList(
            [
                'filter' => [
                    '=ENTITY_ID' => Company::COMPANY_ENTITY_ID,
                ],
                'order'  => [
                    'SORT' => 'ASC',
                ],
            ]
        );
        while ($status = $list->fetch()) {
            $result[$status['STATUS_ID']] = $status;
        }

        return $result;
    }

    /**
     * Метод возвращает предустановленный набор для коллекции.
     *
     * @return \Bitrix\Crm\EO_Deal_Query
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDefaultPrepareCollection(): \Bitrix\Main\ORM\Query\Query
    {
        $date = new \DateTime();
        $date->modify('-3 month');

        return \Onizamov\Reports\Classes\Crm\Company\CompanyTable::query()
            ->setSelect(
                [
                    Company::COMPANY_CODE,
                    CompanyTable::CATEGORY,
                    CompanyTable::STATUS,
                    CompanyTable::LOCATION,
                    CompanyTable::COORDINATE,
                    Company::ACTUAL_ADDRESS_CODE,
                    Company::DATE_OF_CREATION_CODE,
                    Company::COMPANY_TITLE,
                    Company::CATEGORY_CODE,
                ]
            )
            ->whereNotNull(Company::ACTUAL_ADDRESS_CODE)
            ->whereNotNull(Company::LOCATION_CODE)
            ->whereNotNull(Company::STATUS_CODE)
            ->whereNotNull(Company::CATEGORY_CODE)
            ->where(Company::DATE_OF_CREATION_CODE, '>=', \Bitrix\Main\Type\DateTime::createFromPhp($date));
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param \Bitrix\Crm\EO_Company $companyObj
     * @return array
     */
    private function getPreparedFields(\Bitrix\Crm\EO_Company $companyObj): array
    {
        $row = [];

        $row[Company::COMPANY_CODE] = '<a href="/crm/company/details/' . $companyObj->getId() . '/" >' .
            $companyObj->getTitle() . '</a>';

        if (!empty($companyObj->get(CompanyTable::CATEGORY))) {
            $row[Company::CATEGORY_CODE] = $companyObj->get(CompanyTable::CATEGORY)->getName();
        }

        if (!empty($companyObj->get(CompanyTable::STATUS))) {
            $row[Company::STATUS_CODE] = $companyObj->get(CompanyTable::STATUS)->getValue();
        }

        if (!empty($companyObj->get(CompanyTable::LOCATION))) {
            $row[Company::LOCATION_CODE] = $companyObj->get(CompanyTable::LOCATION)->getName();
        }

        $actualAddress = $row[Company::ACTUAL_ADDRESS_CODE] = $companyObj->get(Company::ACTUAL_ADDRESS_CODE);
        if (!empty($companyObj->get(CompanyTable::COORDINATE))) {
            $this->arrCoordinates[$companyObj->getId()]['LATITUDE'] = $companyObj->get(CompanyTable::COORDINATE)->get(
                'LATITUDE'
            );
            $this->arrCoordinates[$companyObj->getId()]['LONGITUDE'] = $companyObj->get(CompanyTable::COORDINATE)->get(
                'LONGITUDE'
            );
            $this->arrCoordinates[$companyObj->getId()]['NAME'] = '<b>Компания:</b> ' . $companyObj->getTitle() . '<br>'
                . '<b>Адрес компании:</b> ' . $row[Company::ACTUAL_ADDRESS_CODE] . '<br>'
                . '<b>Статус:</b> ' . $row[Company::STATUS_CODE] . '<br>';
        } else {
            $this->createCompanyGeoPosition($actualAddress, $companyObj);
        }

        if (!empty($companyObj->get(Company::DATE_OF_CREATION_CODE))) {
            $row[Company::DATE_OF_CREATION_CODE] = $companyObj->get(Company::DATE_OF_CREATION_CODE)->format('d.m.Y');
        }

        return $row;
    }

    /**
     * Установка фильтра c UI.
     *
     * @param $companyPrepareCollection
     * @return mixed
     * @throws \Bitrix\Main\ObjectException
     */
    private function setFilterOptionFromUI(&$companyPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)) {
                continue;
            }
            switch ($key) {
                case Company::STATUS_CODE:
                case Company::LOCATION_CODE:
                case Company::CATEGORY_CODE:
                    $companyPrepareCollection->whereIn($key, $value);
                    break;
            }
        }
    }

    /**
     * Установка пагинации.
     */
    private function setPagination(): void
    {
        $request = Context::getCurrent()->getRequest();
        $this->pageObj->setRecordCount($this->totalCountOfCompanyCollection);
        $this->arResult['PAGINATION'] = [
            'TOTAL'            => $this->totalCountOfCompanyCollection,
            'PAGE_NUM'         => $this->pageObj->getCurrentPage(),
            'ENABLE_NEXT_PAGE' => $this->pageObj->getCurrentPage() < $this->pageObj->getPageCount(),
            'URL'              => $request->getRequestedPage(),
        ];
    }

    /**
     *  Установка настроек пагинации.
     */
    private function setPaginationSettings(): void
    {
        $gridOptions = new \CGridOptions($this->arResult["GRID_ID"]);
        $arNav = $gridOptions->GetNavParams();
        $request = Context::getCurrent()->getRequest();
        $this->pageObj = new PageNavigation('');
        $this->pageObj->setPageSize($arNav['nPageSize']);
        $this->nPageSize = $arNav['nPageSize'];

        if ($request->offsetExists('page')) {
            $page = $request->get('page');
            $this->pageObj->setCurrentPage(
                $page > 0 ? $page : $this->pageObj->getPageCount()
            );
        } else {
            $page = 1;
            $this->pageObj->setCurrentPage($page);
        }
        $this->currentOffset = ($page - 1) * $this->nPageSize;
    }

    /**
     * Установка сортировки элементов.
     *
     * @param $companyPrepareCollection
     */
    private function setSortFromUI(&$companyPrepareCollection): void
    {
        $gridOptions = new \CGridOptions($this->arResult["GRID_ID"]);
        $aSort = $gridOptions->GetSorting(
            [
                "sort" => [CompanyTable::CATEGORY . '.NAME' => "desc"],
                "vars" => ["by" => "by", "order" => "order"],
            ]
        );
        $this->arResult['SORT'] = $aSort['sort'];

        switch (key($aSort['sort'])) {
            case Company::STATUS_CODE:
            case Company::LOCATION_CODE:
                $companyPrepareCollection->setOrder([key($aSort['sort']) . '.TITLE' => current($aSort['sort'])]);
                break;
            case Company::CATEGORY_NAME:
                $companyPrepareCollection->setOrder([key($aSort['sort']) . '.NAME' => current($aSort['sort'])]);
                break;
            default:
                $companyPrepareCollection->setOrder($aSort['sort']);
                break;
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
        return \Bitrix\Main\Config\Option::get('onizamov.reports', 'geocoder_api_key');
    }

    /**
     * Метод получения данных из Яндекса.Геопозиции и сохранения данных.
     *
     * @param $actualAddress
     * @param EO_Company $companyObj
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    private function createCompanyGeoPosition($actualAddress, EO_Company $companyObj): void
    {
        $params = [
            'geocode' => str_replace(" ", "+", $actualAddress),   // адрес
            'format'  => 'json',
            'results' => 1,
            'apikey'  => NewClientsComponent::getYandexGeocoderApiKey(),
        ];

        $response = json_decode(
            file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&'))
        );

        if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
            [$longitude, $latitude] = explode(
                " ",
                $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos
            );
            CompanyLocationsTable::add(
                ['ID' => $companyObj->getId(), 'LATITUDE' => $longitude, 'LONGITUDE' => $latitude]
            );
        }
    }

    /**
     * Метод определения AJAX запросов
     */
    public function configureActions()
    {
        return [
            'sendMessage' => [
                'prefilters' => [
                    new Authentication,
                    new HttpMethod(
                        [
                            HttpMethod::METHOD_POST,
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Метод ajax запроса
     */
    public function sendMessageAction($post)
    {
        Loader::includeModule("onizamov.reports");
        Loader::includeModule("crm");
        foreach ($post as $key => $id) {
            if (!is_numeric($id)) {
                unset($post[$key]);
            }
        }

        $ajaxArrCoordinates = [];
        $companyPrepareCollection = CompanyTable::query()
            ->setSelect(
                [
                    Company::COMPANY_CODE,
                    CompanyTable::CATEGORY,
                    CompanyTable::STATUS,
                    CompanyTable::LOCATION,
                    CompanyTable::COORDINATE,
                    Company::ACTUAL_ADDRESS_CODE,
                    Company::DATE_OF_CREATION_CODE,
                    Company::COMPANY_TITLE,
                    Company::CATEGORY_CODE,
                ]
            );
        $companyPrepareCollection->whereIn(Company::COMPANY_CODE, $post);
        $companyCollection = $companyPrepareCollection->fetchCollection();
        foreach ($companyCollection as $companyObj) {
            if (!empty($companyObj->get(CompanyTable::STATUS))) {
                $status = $companyObj->get(CompanyTable::STATUS)->getValue();
            }

            $actualAddress = $companyObj->get(Company::ACTUAL_ADDRESS_CODE);

            if (!empty($companyObj->get(CompanyTable::COORDINATE))) {
                $ajaxArrCoordinates[$companyObj->getId()]['LATITUDE'] = $companyObj->get(CompanyTable::COORDINATE)->get(
                    'LATITUDE'
                );
                $ajaxArrCoordinates[$companyObj->getId()]['LONGITUDE'] = $companyObj->get(
                    CompanyTable::COORDINATE
                )->get(
                    'LONGITUDE'
                );
                $ajaxArrCoordinates[$companyObj->getId()]['NAME'] = '<b>Компания:</b> ' . $companyObj->getTitle(
                    ) . '<br>'
                    . '<b>Адрес компании:</b> ' . $actualAddress . '<br>'
                    . '<b>Статус:</b> ' . $status . '<br>';
            }
        }
        return $ajaxArrCoordinates;
    }

}
