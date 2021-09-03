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
class RequestToAnotherLocationComponent extends CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'request_to_another_location';
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
            $this->addComments();
            $this->setHeadersAndPanels();
            $this->getData();
            $this->includeComponentTemplate();
        }
    }

    /**
     * Метод обновления комменатриев у сделки.
     *
     * @throws Exception
     */
    private function addComments()
    {
        $fields = Context::getCurrent()->getRequest()->get('FIELDS');
        if (!empty($fields)) {
            foreach ($fields as $id => $data) {
                $CCrmDeal = new \CCrmDeal;
                $CCrmDeal->Update($id, $data);
            }
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

        $this->arResult['GRID_ID'] = 'request_to_another_location';
        $this->arResult['HEADERS'] = [
            [
                'id'      => RequestDeal::LOCATION_PSP_CODE,
                'name'    => RequestDeal::LOCATION_PSP_NAME,
                'sort'    => RequestDeal::LOCATION_PSP_CODE,
                'default' => true,
                'type'    => 'list',
                'items'   => $arrLocations,
            ],
            [
                'id'      => RequestDeal::RESP_MANAGER_CODE,
                'name'    => RequestDeal::RESP_MANAGER_NAME,
                'sort'    => RequestDeal::RESP_MANAGER_CODE,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
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
                'prefix'  => 'U',
            ],
            [
                'id'      => RequestDeal::COMPANY_CODE,
                'name'    => RequestDeal::COMPANY_NAME,
                'sort'    => RequestDeal::COMPANY_CODE,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'enableUsers'        => 'N',
                    'allowUserSearch'    => 'N',
                    'context'            => 'CRM',
                    'contextCode'        => 'CRM',
                    'multiple'           => 'Y',
                    'enableCrm'          => 'Y',
                    'enableCrmCompanies' => 'Y',
                ],
                'prefix'  => '',
            ],
            [
                'id'   => RequestDeal::TOTAL_NUMBER_OF_M2_CODE,
                'name' => RequestDeal::TOTAL_NUMBER_OF_M2_NAME,
                'sort' => false,
            ],
            [
                'id'   => RequestDeal::TRANSFER_DATE_CODE,
                'name' => RequestDeal::TRANSFER_DATE_NAME,
                'sort' => RequestDeal::TRANSFER_DATE_CODE,
                'type' => 'date',
            ],
            [
                'id'    => RequestDeal::LOCATION_ASP_CODE,
                'name'  => RequestDeal::LOCATION_ASP_NAME,
                'sort'  => RequestDeal::LOCATION_ASP_CODE,
                'type'  => 'list',
                'items' => $arrLocations,
            ],
            [
                'id'      => RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE,
                'name'    => RequestDeal::NEW_LOCATION_RESP_MANAGER_NAME,
                'sort'    => RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE,
                'default' => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'context'                 => 'FILTER_' . RequestDeal::NEW_LOCATION_RESP_MANAGER_NAME,
                    'multiple'                => 'Y',
                    'contextCode'             => 'U',
                    'enableAll'               => 'N',
                    'enableSonetgroups'       => 'N',
                    'allowEmailInvitation'    => 'N',
                    'allowSearchEmailUsers'   => 'N',
                    'departmentSelectDisable' => 'Y',
                    'isNumeric'               => 'Y',
                ],
                'prefix'  => 'U',
            ],
            [
                'id'   => RequestDeal::LAUNCH_DATE_CODE,
                'name' => RequestDeal::LAUNCH_DATE_NAME,
                'sort' => false,
            ],
            [
                'id'       => RequestDeal::COMMENTS_CODE,
                'name'     => RequestDeal::COMMENTS_NAME,
                'sort'     => false,
                'editable' => ["size" => 20, "maxlength" => 255],
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
        foreach ($dealsCollection as $dealObj) {
            $this->arResult['ROWS'][] = [
                'id'      => $dealObj->getId(),
                'columns' => $this->getPreparedFields($dealObj),
            ];
        }
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
                    RequestDealTable::IS_PASSED,
                    RequestDealTable::LOCATION_PSP,
                    RequestDealTable::LOCATION_ASP,
                    RequestDealTable::TOTAL_NUMBER_OF_M_2,
                    RequestDealTable::TRANSFER_DATE,
                    RequestDealTable::NEW_LOCATION_RESP_MANAGER,
                    RequestDeal::COMMENTS_CODE,
                    RequestDeal::LAUNCH_DATE_CODE,
                    RequestDeal::COMPANY_CODE,
                    RequestDeal::RESP_MANAGER_CODE,
                ]
            )
            ->whereNotNull(RequestDeal::IS_PASSED_CODE)
            ->whereNotNull(RequestDeal::TRANSFER_DATE_CODE)
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
        $dealRow['ID'] = $dealObj->getId();

        if (!empty($dealObj->get(RequestDealTable::LOCATION_PSP))) {
            $dealRow[RequestDeal::LOCATION_PSP_CODE] = $dealObj->get(RequestDealTable::LOCATION_PSP)->getName();
        }

        if (!empty($dealObj->get(RequestDealTable::LOCATION_ASP))) {
            $dealRow[RequestDeal::LOCATION_ASP_CODE] = $dealObj->get(RequestDealTable::LOCATION_ASP)->getName();
        }

        if (!empty($dealObj->get(RequestDealTable::TOTAL_NUMBER_OF_M_2))) {
            $dealRow[RequestDeal::TOTAL_NUMBER_OF_M2_CODE] = $dealObj->get(RequestDealTable::TOTAL_NUMBER_OF_M_2);
        }

        if (!empty($dealObj->get(RequestDealTable::TRANSFER_DATE))) {
            $dealRow[RequestDeal::TRANSFER_DATE_CODE] = $dealObj->get(RequestDealTable::TRANSFER_DATE);
        }

        if (!empty($dealObj->get(RequestDealTable::NEW_LOCATION_RESP_MANAGER))) {
            $dealRow[RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE] =
                $dealObj->get(RequestDealTable::NEW_LOCATION_RESP_MANAGER)->getLastName() . ' ' .
                $dealObj->get(RequestDealTable::NEW_LOCATION_RESP_MANAGER)->getName();
        }

        if (!empty($dealObj->get(RequestDeal::COMPANY_CODE))) {
            $dealRow[RequestDeal::COMPANY_CODE] = $dealObj->get(RequestDeal::COMPANY_CODE)->getTitle();
        }

        $dealRow[RequestDeal::COMMENTS_CODE] = $dealObj->get(RequestDeal::COMMENTS_CODE);
        $dealRow[RequestDeal::LAUNCH_DATE_CODE] = $dealObj->get(RequestDeal::LAUNCH_DATE_CODE)->format('d.m.Y');

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
                case RequestDeal::LOCATION_PSP_CODE:
                case RequestDeal::LOCATION_ASP_CODE:
                    $dealsPrepareCollection->where($key, $value);
                    break;
                case RequestDeal::RESP_MANAGER_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $dealsPrepareCollection->whereIn(RequestDeal::ASSIGNED_BY_ID_CODE, $value);
                    break;
                case RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $dealsPrepareCollection->whereIn(RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE, $value);
                    break;
                case RequestDeal::COMPANY_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMCOMPANY', '', $item);
                        }
                    );
                    $dealsPrepareCollection->whereIn(RequestDeal::COMPANY_ID, $value);
                    break;
                case RequestDeal::TRANSFER_DATE_CODE . '_from':
                    $dealsPrepareCollection->where(
                        RequestDeal::TRANSFER_DATE_CODE,
                        ">=",
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case RequestDeal::TRANSFER_DATE_CODE . '_to':
                    $dealsPrepareCollection->where(
                        RequestDeal::TRANSFER_DATE_CODE,
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
            ["sort" => [RequestDeal::LOCATION_PSP_CODE => "desc"], "vars" => ["by" => "by", "order" => "order"]]
        );
        $this->arResult['SORT'] = $aSort['sort'];

        switch (key($aSort['sort'])) {
            case RequestDeal::LOCATION_PSP_CODE:
            case RequestDeal::LOCATION_ASP_CODE:
            case RequestDeal::COMPANY_CODE:
                $dealsPrepareCollection->setOrder([key($aSort['sort']) . '.TITLE' => current($aSort['sort'])]);
                break;
            case RequestDeal::RESP_MANAGER_CODE:
            case RequestDeal::NEW_LOCATION_RESP_MANAGER_CODE:
                $dealsPrepareCollection->setOrder([key($aSort['sort']) . '.LAST_NAME' => current($aSort['sort'])]);
                break;
            default:
                $dealsPrepareCollection->setOrder($aSort['sort']);
                break;
        }
    }

}
