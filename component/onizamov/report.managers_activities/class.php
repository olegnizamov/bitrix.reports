<?php

use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Activity\Activity;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class RequestToAnotherLocationComponent - компонент Отчет о передаче запросов в другую локацию.
 */
class ManagerActivitiesComponent extends CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'managers_activities';
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

        $arrStatuses = $this->getFailedStatuses();

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['HEADERS'] = [
            [
                'id'   => RequestDeal::MANAGER_CODE,
                'name' => RequestDeal::MANAGER_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::REQUEST_ID_CODE,
                'name' => RequestDeal::REQUEST_ID_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_CALLS_CODE,
                'name' => RequestDeal::NUMBER_OF_CALLS_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_MEETINGS_CODE,
                'name' => RequestDeal::NUMBER_OF_MEETINGS_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_EMAILS_CODE,
                'name' => RequestDeal::NUMBER_OF_EMAILS_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::REASON_OF_FAILED_REQUEST_CODE,
                'name' => RequestDeal::REASON_OF_FAILED_REQUEST_NAME,
                'sort'   => RequestDeal::REASON_OF_FAILED_REQUEST_CODE,
                'type'   => 'list',
                'items'  => $arrStatuses,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_DEALS_CODE,
                'name' => RequestDeal::NUMBER_OF_DEALS_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_SUCCESS_DEALS_CODE,
                'name' => RequestDeal::NUMBER_OF_SUCCESS_DEALS_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::NUMBER_OF_FAILED_DEALS_CODE,
                'name' => RequestDeal::NUMBER_OF_FAILED_DEALS_NAME,
                'sort' => false,
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

        foreach ($arrRows as $requestDeals) {
            $conclusionRequestDealString = [];
            $amountOfRequest = 0;
            $amountOfSuccessRequest = 0;
            $amountOfFailedRequest = 0;
            foreach ($requestDeals as $deal) {
                $this->arResult['ROWS'][] = $deal;
                $amountOfSuccessRequest += $deal['columns'][RequestDeal::IS_SUCCESS_DEAL_CODE];
                $amountOfFailedRequest += $deal['columns'][RequestDeal::IS_FAILED_DEAL_CODE];
                $amountOfRequest++;
            }
            if (!empty($deal['columns'])) {
                $conclusionRequestDealString['columns'][RequestDeal::RESP_MANAGER_CODE] = $deal['columns'][RequestDeal::RESP_MANAGER_CODE];
                $conclusionRequestDealString['columns'][RequestDeal::NUMBER_OF_DEALS_CODE] = $amountOfRequest;
                $conclusionRequestDealString['columns'][RequestDeal::NUMBER_OF_SUCCESS_DEALS_CODE] = $amountOfSuccessRequest;
                $conclusionRequestDealString['columns'][RequestDeal::NUMBER_OF_FAILED_DEALS_CODE] = $amountOfFailedRequest;
                $conclusionRequestDealString['columns'][RequestDeal::REASON_OF_FAILED_REQUEST_CODE] = '-';
                $conclusionRequestDealString['columns'][RequestDeal::REQUEST_ID_CODE] = '-';
                $this->arResult['ROWS'][] = $conclusionRequestDealString;
                $this->totalCountOfDealCollection++;
            }
        }

        $this->setPagination();
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
                    RequestDeal::REQUEST_ID_CODE,
                    RequestDeal::REQUEST_TITLE,
                    RequestDeal::RESP_MANAGER_CODE,
                    RequestDealTable::DEAL_STATUS,
                    RequestDealTable::ACTIVITY,
                ]
            )
            ->where('CATEGORY_ID', RequestDeal::CATEGORY_ID_REQUEST)
            ->whereIn(RequestDeal::STAGE_SEMANTIC_ID_CODE, ['S', 'F']);
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

        if (!empty($dealObj->get(RequestDealTable::DEAL_STATUS))) {
            $dealRow[RequestDeal::IS_SUCCESS_DEAL_CODE] = $dealObj->get(RequestDealTable::DEAL_STATUS)
                ->getSemantics() === 'S' ? 1 : 0;
            $dealRow[RequestDeal::IS_FAILED_DEAL_CODE] = $dealObj->get(RequestDealTable::DEAL_STATUS)
                ->getSemantics() === 'F' ? 1 : 0;
            $dealRow[RequestDeal::REASON_OF_FAILED_REQUEST_CODE] = $dealObj->get(RequestDealTable::DEAL_STATUS)
                ->getSemantics() === 'F' ? $dealObj->get('DEAL_STATUS')->getName() : '';
        }

        $dealRow[RequestDeal::NUMBER_OF_CALLS_CODE] = 0;
        $dealRow[RequestDeal::NUMBER_OF_MEETINGS_CODE] = 0;
        $dealRow[RequestDeal::NUMBER_OF_EMAILS_CODE] = 0;
        foreach ($dealObj->get(RequestDealTable::ACTIVITY) as $activity) {
            if($activity->getProviderTypeId() ===  Activity::IS_MEETING){
                $dealRow[RequestDeal::NUMBER_OF_MEETINGS_CODE]++;
            }
            if($activity->getProviderTypeId() ===  Activity::IS_CALL){
                $dealRow[RequestDeal::NUMBER_OF_CALLS_CODE]++;
            }
            if($activity->getProviderTypeId() ===  Activity::IS_EMAIL){
                $dealRow[RequestDeal::NUMBER_OF_EMAILS_CODE]++;
            }
        }

        if (!empty($dealObj->get(RequestDeal::RESP_MANAGER_CODE))) {
            $dealRow[RequestDeal::RESP_MANAGER_CODE] =
                $dealObj->get(RequestDeal::RESP_MANAGER_CODE)->getLastName() . ' ' .
                $dealObj->get(RequestDeal::RESP_MANAGER_CODE)->getName();
        }
        return $dealRow;
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
     * Метод получения статусов.
     *
     * @return array
     */
    private function getFailedStatuses(): array
    {
        $result = [];
        $statusCollection =  \Bitrix\Crm\StatusTable::query()
            ->setSelect(
                ['STATUS_ID', 'NAME']
            )
            ->where('ENTITY_ID', RequestDeal::DEAL_STAGE)
            ->where('SEMANTICS', RequestDeal::DEAL_STAGE_FINAL)
            ->fetchCollection();

        foreach ($statusCollection as $statusObj) {
            $result[$statusObj->getStatusId()] = $statusObj->getName();
        }
        return $result;
    }

    /**
     * Установка фильтра c UI.
     *
     * @param $dealPrepareCollection
     * @return mixed
     * @throws \Bitrix\Main\ObjectException
     */
    private function setFilterOptionFromUI(&$dealPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)) {
                continue;
            }
            switch ($key) {
                case RequestDeal::REASON_OF_FAILED_REQUEST_CODE:
                    $dealPrepareCollection->whereIn(RequestDeal::STAGE_ID_CODE, $value);
                    break;
            }
        }
    }

    /**
     * Установка сортировки элементов.
     *
     * @param $dealPrepareCollection
     */
    private function setSortFromUI(&$dealPrepareCollection): void
    {
        $gridOptions = new CGridOptions($this->arResult["GRID_ID"]);
        $aSort = $gridOptions->GetSorting(
            [
                "sort" => [RequestDeal::REASON_OF_FAILED_REQUEST_CODE . '.NAME' => "desc"],
                "vars" => ["by" => "by", "order" => "order"],
            ]
        );
        $this->arResult['SORT'] = $aSort['sort'];

        switch (key($aSort['sort'])) {
            case RequestDeal::REASON_OF_FAILED_REQUEST_CODE:
                $dealPrepareCollection->setOrder([RequestDealTable::DEAL_STATUS . '.NAME' => current($aSort['sort'])]);
                break;
        }
    }
}
