<?php

use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class RequestToAnotherLocationComponent - компонент Отчет о передаче запросов в другую локацию.
 */
class AverageProcessingTimeComponent extends CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'average_processing_time';
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
    private $totalCountOfDealCollection;

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
        $arrRequestTypeElements = $this->getRequestTypeElements();

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['HEADERS'] = [
            [
                'id'     => RequestDeal::RESP_MANAGER_CODE,
                'name'   => RequestDeal::RESP_MANAGER_NAME,
                'sort'   => RequestDeal::RESP_MANAGER_CODE,
                'type'   => 'dest_selector',
                'params' => [
                    'context'                 => 'FILTER_' . RequestDeal::RESP_MANAGER_CODE,
                    'multiple'                => 'Y',
                    'contextCode'             => 'U',
                    'enableAll'               => 'N',
                    'enableSonetgroups'       => 'N',
                    'allowEmailInvitation'    => 'N',
                    'allowSearchEmailUsers'   => 'N',
                    'departmentSelectDisable' => 'Y',
                    'isNumeric'               => 'Y',
                ],
                'prefix' => 'U',
            ],
            [
                'id'      => RequestDeal::REQUEST_ID_CODE,
                'name'    => RequestDeal::REQUEST_ID_NAME,
                'sort'    => RequestDeal::REQUEST_ID_CODE,
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
                'id'    => RequestDeal::REQUEST_TYPE_CODE,
                'name'  => RequestDeal::REQUEST_TYPE_NAME,
                'sort'  => RequestDeal::REQUEST_TYPE_CODE,
                'type'  => 'list',
                'items' => $arrRequestTypeElements,
            ],
            [
                'id'   => RequestDeal::BEGIN_DATE_CODE,
                'name' => RequestDeal::BEGIN_DATE_NAME,
                'sort' => RequestDeal::BEGIN_DATE_CODE,
                'type' => 'date',
            ],
            [
                'id'   => RequestDeal::CLOSE_DATE_CODE,
                'name' => RequestDeal::CLOSE_DATE_NAME,
                'sort' => RequestDeal::CLOSE_DATE_CODE,
                'type' => 'date',
            ],
            [
                'id'   => RequestDeal::PEDIOD_CODE,
                'name' => RequestDeal::PEDIOD_NAME,
                'sort' => RequestDeal::PEDIOD_CODE,
                'type' => 'number',
            ],
        ];

        $this->arResult['FILTERS'] = $this->arResult['HEADERS'];
        foreach ($this->arResult['FILTERS'] as $key => &$filter) {
            if (empty($filter['sort'])) {
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
        $dealsPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setSortFromUI($dealsPrepareCollection);
        $this->setFilterOptionFromUI($dealsPrepareCollection);
        $this->setPaginationSettings();
        $dealsPrepareCollection->countTotal(true);
        $dealsPrepareCollection->setOffset($this->currentOffset);
        $dealsPrepareCollection->setLimit($this->nPageSize);
        $this->totalCountOfDealCollection = $dealsPrepareCollection->exec()->getCount();
        $dealsCollection = $dealsPrepareCollection->fetchCollection();

        //сделал хак- сортировка по ответсвенному + результатирующая строка, пересобрав  массивы
        $arrRows = [];
        foreach ($dealsCollection as $dealObj) {
            $arrRows[$dealObj->get(RequestDeal::RESP_MANAGER_CODE)->getId()][] = [
                'id'      => $dealObj->getId(),
                'columns' => $this->getPreparedFields($dealObj),
            ];
        }

        foreach ($arrRows as $managerId => $requestDeals) {
            $conclusionRequestDealString = [];
            $amountOfRequest = 0;
            $amountOfTime = 0;
            foreach ($requestDeals as $deal) {
                $this->arResult['ROWS'][] = $deal;
                $amountOfRequest++;
                $amountOfTime += $deal['columns'][RequestDeal::PEDIOD_CODE];
            }
            if (!empty($deal['columns'])) {
                $conclusionRequestDealString['columns'][RequestDeal::RESP_MANAGER_CODE] = $deal['columns'][RequestDeal::RESP_MANAGER_CODE];
                $conclusionRequestDealString['columns'][RequestDeal::REQUEST_ID_CODE] = $amountOfRequest;
                $conclusionRequestDealString['columns'][RequestDeal::PEDIOD_CODE] = round(
                    $amountOfTime / $amountOfRequest,
                    2
                );
                $this->arResult['ROWS'][] = $conclusionRequestDealString;
                $this->totalCountOfDealCollection++;
            }
        }

        $this->setPagination();
    }

    /**
     * Метод получения элементов Типа запроса.
     *
     * @return array
     */
    private function getRequestTypeElements(): array
    {
        $result = [];
        $fieldDb = \CUserFieldEnum::GetList([], ['USER_FIELD_ID' => RequestDeal::REQUEST_TYPE_ID]);
        while ($field = $fieldDb->Fetch()) {
            $result[$field['ID']] = $field['VALUE'];
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
        return RequestDealTable::query()
            ->setSelect(
                [
                    RequestDeal::RESP_MANAGER_CODE,
                    RequestDealTable::REQUEST_TYPE,
                    RequestDeal::BEGIN_DATE_CODE,
                    RequestDeal::CLOSE_DATE_CODE,
                    RequestDeal::REQUEST_TITLE,
                    RequestDealTable::PERIOD_BETWEEN,
                ]
            )
            ->whereNotNull(RequestDeal::CLOSE_DATE_CODE)
            ->where('CATEGORY_ID', RequestDeal::CATEGORY_ID_REQUEST);
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param \Bitrix\Crm\EO_Deal $dealObj
     * @return array
     */
    private function getPreparedFields(\Bitrix\Crm\EO_Deal $dealObj): array
    {
        $dealRow = [];
        $dealRow[RequestDeal::REQUEST_ID_CODE] = '<a href="/crm/deal/details/' . $dealObj->getId() . '/" >' .
            $dealObj->getTitle() . '</a>';
        if (!empty($dealObj->get(RequestDealTable::REQUEST_TYPE))) {
            $dealRow[RequestDeal::REQUEST_TYPE_CODE] = $dealObj->get(RequestDealTable::REQUEST_TYPE)->getValue();
        }
        $dealRow[RequestDeal::BEGIN_DATE_CODE] = $dealObj->get(RequestDeal::BEGIN_DATE_CODE)->format('d.m.Y');
        $dealRow[RequestDeal::CLOSE_DATE_CODE] = $dealObj->get(RequestDeal::CLOSE_DATE_CODE)->format('d.m.Y');
        $dealRow[RequestDeal::PEDIOD_CODE] = $dealObj->get(RequestDealTable::PERIOD_BETWEEN) > 0 ? $dealObj->get(
            RequestDealTable::PERIOD_BETWEEN
        ) : 1;
        if (!empty($dealObj->get(RequestDeal::RESP_MANAGER_CODE))) {
            $dealRow[RequestDeal::RESP_MANAGER_CODE] =
                $dealObj->get(RequestDeal::RESP_MANAGER_CODE)->getLastName() . ' ' .
                $dealObj->get(RequestDeal::RESP_MANAGER_CODE)->getName();
        }
        return $dealRow;
    }

    /**
     * Установка фильтра c UI.
     *
     * @param $dealsPrepareCollection
     * @return mixed
     * @throws \Bitrix\Main\ObjectException
     */
    private function setFilterOptionFromUI(&$dealsPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)) {
                continue;
            }
            switch ($key) {
                case RequestDeal::RESP_MANAGER_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $dealsPrepareCollection->whereIn(RequestDeal::ASSIGNED_BY_ID_CODE, $value);
                    break;
                case RequestDeal::REQUEST_ID_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMDEAL', '', $item);
                        }
                    );
                    $dealsPrepareCollection->whereIn(RequestDeal::REQUEST_ID_CODE, $value);
                    break;
                case RequestDeal::REQUEST_TYPE_CODE:
                    $dealsPrepareCollection->where($key, $value);
                    break;
                case RequestDeal::PEDIOD_CODE . '_from':
                case RequestDeal::PEDIOD_CODE . '_to':
                    $operation = '';
                    $operationValue = '';
                    switch ($filterOps[RequestDeal::PEDIOD_CODE . '_numsel']) {
                        case 'less':
                            $operation = '<';
                            $operationValue = $filterOps[RequestDeal::PEDIOD_CODE . '_to'];
                            break;
                        case 'more':
                            $operation = '>';
                            $operationValue = $filterOps[RequestDeal::PEDIOD_CODE . '_from'];
                            break;
                        case 'exact':
                            $operation = '=';
                            $operationValue = $filterOps[RequestDeal::PEDIOD_CODE . '_from'];
                            break;
                        case 'range':
                            if (empty($filterOps[RequestDeal::PEDIOD_CODE . '_from'])) {
                                $operation = '<';
                                $operationValue = $filterOps[RequestDeal::PEDIOD_CODE . '_to'];
                            } elseif (empty($filterOps[RequestDeal::PEDIOD_CODE . '_to'])) {
                                $operation = '>';
                                $operationValue = $filterOps[RequestDeal::PEDIOD_CODE . '_from'];
                            } else {
                                $operation = 'between';
                            }
                            break;
                    }
                    if (!empty($operation) && in_array($operation, ['<', '>', '='])) {
                        $dealsPrepareCollection->where(
                            RequestDeal::PEDIOD_CODE,
                            $operation,
                            $operationValue
                        );
                    } elseif ($operation === 'between') {
                        $dealsPrepareCollection->whereBetween(
                            RequestDeal::PEDIOD_CODE,
                            $filterOps[RequestDeal::PEDIOD_CODE . '_from'],
                            $filterOps[RequestDeal::PEDIOD_CODE . '_to']
                        );
                    }
                    break;
                case RequestDeal::BEGIN_DATE_CODE . '_from':
                case RequestDeal::CLOSE_DATE_CODE . '_from':
                    $key = str_replace('_from', '', $key);
                    $dealsPrepareCollection->where(
                        $key,
                        ">=",
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case RequestDeal::BEGIN_DATE_CODE . '_to':
                case RequestDeal::CLOSE_DATE_CODE . '_to':
                    $key = str_replace('_to', '', $key);
                    $dealsPrepareCollection->where(
                        $key,
                        "<=",
                        new \Bitrix\Main\Type\DateTime($value)
                    );
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
        $this->pageObj->setRecordCount($this->totalCountOfDealCollection);
        $this->arResult['PAGINATION'] = [
            'TOTAL'            => $this->totalCountOfDealCollection,
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
        $gridOptions = new CGridOptions($this->arResult["GRID_ID"]);
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
     * @param $dealsPrepareCollection
     */
    private function setSortFromUI(&$dealsPrepareCollection): void
    {
        $gridOptions = new CGridOptions($this->arResult["GRID_ID"]);
        $aSort = $gridOptions->GetSorting(
            [
                "sort" => [RequestDeal::RESP_MANAGER_CODE . '.LAST_NAME' => "desc"],
                "vars" => ["by" => "by", "order" => "order"],
            ]
        );
        $this->arResult['SORT'] = $aSort['sort'];

        switch (key($aSort['sort'])) {
            case RequestDeal::RESP_MANAGER_CODE:
                $dealsPrepareCollection->setOrder([key($aSort['sort']) . '.LAST_NAME' => current($aSort['sort'])]);
                break;
            case RequestDeal::REQUEST_ID_CODE:
                $dealsPrepareCollection->setOrder([RequestDeal::REQUEST_TITLE => current($aSort['sort'])]);
                break;
            case RequestDeal::REQUEST_TYPE_CODE:
                $dealsPrepareCollection->setOrder(
                    [RequestDealTable::REQUEST_TYPE . '.VALUE' => current($aSort['sort'])]
                );
                break;
            default:
                $dealsPrepareCollection->setOrder($aSort['sort']);
                break;
        }
    }
}
