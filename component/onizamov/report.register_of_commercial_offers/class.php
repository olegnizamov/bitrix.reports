<?php

namespace Onizamov\Components;

use Bitrix\Crm\CompanyTable;
use Bitrix\DocumentGenerator\Model\EO_Document;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Onizamov\Reports\Classes\Crm\Company\Company;
use Onizamov\Reports\Classes\Crm\Deal\Deal;
use Onizamov\Reports\Classes\Crm\Document\Document;
use Onizamov\Reports\Classes\Crm\Document\DocumentTable;
use Bitrix\Iblock\Elements\ElementLocationsTable;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class RegisterOfCommercialOffersComponent - Реестр КП.
 */
class RegisterOfCommercialOffersComponent extends \CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'register_of_commercial_offers';
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
    private $totalCountOfCollection;

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
        if (Loader::includeModule("crm")
            && Loader::includeModule("documentgenerator")
            && Loader::includeModule("onizamov.reports")
        ) {
            $this->setHeadersAndPanels();
            $this->getData();
            $this->includeComponentTemplate();
        }
    }

    /**
     * Метод форматирования Заголовков таблицы и панели редактирования.
     */
    public function setHeadersAndPanels()
    {
        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['HEADERS'] = [
            [
                'id'     => Document::COMPANY_CODE,
                'name'   => Document::COMPANY_NAME,
                'filter' => true,
                'type'   => 'dest_selector',
                'params' => [
                    'enableUsers'        => 'N',
                    'allowUserSearch'    => 'N',
                    'context'            => 'CRM',
                    'contextCode'        => 'CRM',
                    'multiple'           => 'Y',
                    'enableCrm'          => 'Y',
                    'enableCrmDeals'     => 'N',
                    'enableCrmCompanies' => 'Y',
                ],
                'prefix' => '',
            ],
            [
                'id'      => Document::RESPONSIBLE_CODE,
                'name'    => Document::RESPONSIBLE_NAME,
                'filter'  => true,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'enableUsers'     => 'Y',
                    'allowUserSearch' => 'Y',
                    'context'         => 'CRM',
                    'contextCode'     => 'CRM',
                    'multiple'        => 'Y',
                    'enableCrm'       => 'Y',
                    'enableCrmDeals'  => 'N',
                ],
                'prefix'  => '',
            ],
            [
                'id'      => Document::DEAL_CODE,
                'name'    => Document::DEAL_NAME,
                'filter'  => true,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'enableUsers'     => 'N',
                    'allowUserSearch' => 'N',
                    'context'         => 'CRM',
                    'contextCode'     => 'CRM',
                    'multiple'        => 'Y',
                    'enableCrm'       => 'Y',
                    'enableCrmDeals'  => 'Y',
                ],
                'prefix'  => '',
            ],
            [
                'id'      => Document::NUMBER_DOCUMENT_CODE,
                'name'    => Document::NUMBER_DOCUMENT_NAME,
                'filter'  => true,
                'default' => true,
                'type'    => 'string',
            ],
            [
                'id'      => RequestDeal::REQUEST_DEAL_ID_CODE,
                'name'    => RequestDeal::REQUEST_DEAL_ID_NAME,
                'filter'  => true,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'enableUsers'     => 'N',
                    'allowUserSearch' => 'N',
                    'context'         => 'CRM',
                    'contextCode'     => 'CRM',
                    'multiple'        => 'Y',
                    'enableCrm'       => 'Y',
                    'enableCrmDeals'  => 'Y',
                ],
                'prefix'  => '',
            ],
        ];


        $arrLocations = $this->getLocations();
        $arrTypeId = $this->getTypeId();
        $this->arResult['FILTERS'] = array_merge(
            $this->arResult['HEADERS'],
            [
                [
                    'id'     => RequestDeal::TOTAL_NUMBER_OF_M2_CODE,
                    'name'   => RequestDeal::TOTAL_NUMBER_OF_M2_NAME,
                    'sort'   => false,
                    'filter' => true,
                    'type'   => 'number',
                ],
                [
                    'id'     => RequestDeal::BEGIN_DATE_CODE,
                    'name'   => RequestDeal::BEGIN_DATE_NAME,
                    'type'   => 'date',
                    'sort'   => false,
                    'filter' => true,
                ],
                [
                    'id'     => RequestDeal::CLOSE_DATE_CODE,
                    'name'   => RequestDeal::CLOSE_DATE_NAME,
                    'type'   => 'date',
                    'sort'   => false,
                    'filter' => true,
                ],
                [
                    'id'     => RequestDeal::LOCATION_PSP_CODE,
                    'name'   => RequestDeal::LOCATION_PSP_NAME,
                    'sort'   => false,
                    'filter' => true,
                    'type'   => 'list',
                    'items'  => $arrLocations,
                ],
                [
                    'id'     => RequestDeal::OPPORTUNITY_CODE,
                    'name'   => RequestDeal::OPPORTUNITY_NAME,
                    'sort'   => false,
                    'filter' => true,
                    'type'   => 'number',
                ],
                [
                    'id'     => RequestDeal::TYPE_ID_CODE,
                    'name'   => RequestDeal::TYPE_ID_NAME,
                    'sort'   => false,
                    'filter' => true,
                    'type'   => 'list',
                    'items'  => $arrTypeId,
                ],
            ]

        );


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
        $documentPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setFilterOptionForDealFromUI($documentPrepareCollection);
        $this->setPaginationSettings();
        $documentPrepareCollection->countTotal(true);
        $documentPrepareCollection->setOffset($this->currentOffset);
        $documentPrepareCollection->setLimit($this->nPageSize);
        $this->totalCountOfCollection = $documentPrepareCollection->exec()->getCount();

        $documentCollection = $documentPrepareCollection->fetchCollection();
        foreach ($documentCollection as $documentObj) {
            $info = $this->getPreparedFields($documentObj);
            if (!empty($info)) {
                $this->arResult['ROWS'][] = [
                    'id'      => $documentObj->getId(),
                    'columns' => $info,
                ];
            }
        }

        $this->setPagination();
    }


    /**
     * Метод возвращает предустановленный набор для коллекции.
     *
     * @return \Bitrix\DocumentGenerator\Model\EO_Document_Query
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDefaultPrepareCollection(): \Bitrix\Main\ORM\Query\Query
    {
        return DocumentTable::query()
            ->setSelect(
                [
                    Document::NUMBER_DOCUMENT_CODE,
                    Document::ENTITY_ID_CODE,
                    Document::ID_DOCUMENT_CODE,
                    DocumentTable::DEAL,
                    DocumentTable::DEAL . '.' . RequestDealTable::TOTAL_NUMBER_OF_M_2,
                    DocumentTable::DEAL . '.' . RequestDealTable::LOCATION_PSP,
                    DocumentTable::DEAL . '.' . RequestDealTable::TYPE,
                    DocumentTable::DEAL . '.' . RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE,
                    DocumentTable::DEAL . '.' . RequestDeal::REQUEST_DEAL_ID_CODE,
                ]
            )->where('PROVIDER', DocumentTable::DEAL_PROVIDER)
            ->whereIn(
                DocumentTable::DEAL . '.CATEGORY_ID',
                [
                    Deal::CATEGORY_ID_REQUEST,
                    Deal::CATEGORY_ID_SIMPLE_SALES,
                ]
            )
            //  ->whereIn(Document::NUMBER_DOCUMENT_CODE, DocumentTable::DEAL . '.' . RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE)
            ->whereColumn(
                DocumentTable::DEAL . '.' . RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE,
                Document::NUMBER_DOCUMENT_CODE
            )
            ->setOrder([Document::ENTITY_ID_CODE => 'DESC', Document::ID_DOCUMENT_CODE => 'ASC']);
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param EO_Document $documentObj
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getPreparedFields(EO_Document $documentObj): array
    {
        $row = [];

        if (!empty($documentObj->get(DocumentTable::DEAL))) {
            $row[Document::DEAL_CODE] = '<a href="/crm/deal/details/' .
                $documentObj->get(DocumentTable::DEAL)->getId() . '/" >' .
                $documentObj->get(DocumentTable::DEAL)->getTitle() . '</a>';

            foreach ($documentObj->get(DocumentTable::DEAL)->get(RequestDeal::REQUEST_DEAL_ID_CODE) as $requestDeal) {
                $requestDealObj = \CCrmDeal::GetByID($requestDeal);
                $row['~'.RequestDeal::REQUEST_DEAL_ID_CODE][] =
                    '<a href="/crm/deal/details/' . $requestDeal . '/" >' .
                        $requestDealObj['TITLE'] .
                    '</a>';
            }
            $row[RequestDeal::REQUEST_DEAL_ID_CODE] = implode(' , ',$row['~'.RequestDeal::REQUEST_DEAL_ID_CODE]);
            $row[Document::DEAL_CODE_ID] = $documentObj->get(DocumentTable::DEAL)->getId();
            if (!empty($documentObj->get(DocumentTable::DEAL)->get('COMPANY_ID'))) {
                $companyObj = CompanyTable::getById(
                    $documentObj->get(DocumentTable::DEAL)->get('COMPANY_ID')
                )->fetchObject();
                if (!empty($companyObj)) {
                    $row[Document::COMPANY_CODE] = '<a href="/crm/company/details/' .
                        $companyObj->getId() . '/" >' .
                        $companyObj->getTitle() . '</a>';
                }
            }

            if (!empty($documentObj->get(DocumentTable::DEAL)->get('ASSIGNED_BY_ID'))) {
                $row[Document::RESPONSIBLE_CODE_ID] = $documentObj->get(DocumentTable::DEAL)->get('ASSIGNED_BY_ID');
                $userObj = UserTable::getById(
                    $documentObj->get(DocumentTable::DEAL)->get('ASSIGNED_BY_ID')
                )->fetchObject();
                if (!empty($userObj)) {
                    $row[Document::RESPONSIBLE_CODE] = '<a href="/company/personal/user/' .
                        $userObj->getId() . '/" >' .
                        $userObj->getLastName() . ' ' . $userObj->getName() . '</a>';
                }
            }
        }
        $row[Document::NUMBER_DOCUMENT_CODE] = $documentObj->getNumber();
        $row[Document::NUMBER_DOCUMENT_CODE_ID] = $documentObj->getNumber();

        return $row;
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
     * Метод получения локаций.
     *
     * @return array
     */
    private function getTypeId(): array
    {
        $result = [];

        $list = \Bitrix\Crm\StatusTable::getList(
            [
                'filter' => [
                    '=ENTITY_ID' => 'DEAL_TYPE',
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
     * Установка фильтра c UI сущности Сделка.
     *
     * @param $documentPrepareCollection
     * @return mixed
     */
    private function setFilterOptionForDealFromUI(&$documentPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)) {
                continue;
            }
            switch ($key) {
                case RequestDeal::TOTAL_NUMBER_OF_M2_CODE . '_from':
                case RequestDeal::TOTAL_NUMBER_OF_M2_CODE . '_to':
                case RequestDeal::OPPORTUNITY_CODE . '_from':
                case RequestDeal::OPPORTUNITY_CODE . '_to':
                    $operation = '';
                    $operationValue = '';
                    $key = str_replace(['_from', '_to'], '', $key);
                    switch ($filterOps[$key . '_numsel']) {
                        case 'less':
                            $operation = '<';
                            $operationValue = $filterOps[$key . '_to'];
                            break;
                        case 'more':
                            $operation = '>';
                            $operationValue = $filterOps[$key . '_from'];
                            break;
                        case 'exact':
                            $operation = '=';
                            $operationValue = $filterOps[$key . '_from'];
                            break;
                        case 'range':
                            if (empty($filterOps[$key . '_from'])) {
                                $operation = '<';
                                $operationValue = $filterOps[$key . '_to'];
                            } elseif (empty($filterOps[$key . '_to'])) {
                                $operation = '>';
                                $operationValue = $filterOps[$key . '_from'];
                            } else {
                                $operation = 'between';
                            }
                            break;
                    }

                    if (!empty($operation) && in_array($operation, ['<', '>', '='])) {
                        $documentPrepareCollection->where(
                            DocumentTable::DEAL .
                            '.' .
                            ($key === RequestDeal::OPPORTUNITY_CODE ? RequestDeal::OPPORTUNITY_CODE : RequestDealTable::TOTAL_NUMBER_OF_M_2),
                            $operation,
                            $operationValue
                        );
                    } elseif ($operation === 'between') {
                        $documentPrepareCollection->whereBetween(
                            DocumentTable::DEAL .
                            '.' .
                            ($key === RequestDeal::OPPORTUNITY_CODE ? RequestDeal::OPPORTUNITY_CODE : RequestDealTable::TOTAL_NUMBER_OF_M_2),
                            $filterOps[RequestDeal::TOTAL_NUMBER_OF_M2_CODE . '_from'],
                            $filterOps[RequestDeal::TOTAL_NUMBER_OF_M2_CODE . '_to']
                        );
                    }
                    break;
                case RequestDeal::BEGIN_DATE_CODE . '_from':
                case RequestDeal::CLOSE_DATE_CODE . '_from':
                    $key = str_replace('_from', '', $key);
                    $documentPrepareCollection->where(
                        DocumentTable::DEAL . '.' . $key,
                        ">=",
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case RequestDeal::BEGIN_DATE_CODE . '_to':
                case RequestDeal::CLOSE_DATE_CODE . '_to':
                    $key = str_replace('_to', '', $key);
                    $documentPrepareCollection->where(
                        DocumentTable::DEAL . '.' . $key,
                        "<=",
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case RequestDeal::TYPE_ID_CODE:
                case RequestDeal::LOCATION_PSP_CODE:
                    $documentPrepareCollection->where(DocumentTable::DEAL . '.' . $key, $value);
                    break;
                case Document::DEAL_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMDEAL', '', $item);
                        }
                    );
                    $documentPrepareCollection->whereIn(Document::ENTITY_ID_CODE, $value);
                    break;
                case RequestDeal::REQUEST_DEAL_ID_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMDEAL', '', $item);
                        }
                    );
                    $documentPrepareCollection->whereIn(DocumentTable::DEAL . '.' . $key, $value);
                    break;
                case Document::NUMBER_DOCUMENT_CODE:
                    $documentPrepareCollection->where(Document::NUMBER_DOCUMENT_CODE, $value);
                    break;
                case Document::RESPONSIBLE_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $documentPrepareCollection->whereIn(
                        DocumentTable::DEAL . '.' . RequestDeal::ASSIGNED_BY_ID_CODE,
                        $value
                    );
                    break;
                case Document::COMPANY_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMCOMPANY', '', $item);
                        }
                    );
                    $documentPrepareCollection->whereIN(DocumentTable::DEAL . '.COMPANY.ID', $value);
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
        $this->pageObj->setRecordCount($this->totalCountOfCollection);
        $this->arResult['PAGINATION'] = [
            'TOTAL'            => $this->totalCountOfCollection,
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

}
