<?php

namespace Onizamov\Components;

use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\Training\AttemptTable;
use Onizamov\Reports\Classes\Training\GradebookTable;
use Onizamov\Reports\Classes\Training\Gradebook;
use Onizamov\Reports\Classes\Training\TrainingDealConnectionTable;
use Onizamov\Reports\Classes\User\User;
use Onizamov\Reports\Classes\User\UserTable;

/**
 * Class TrainingProgramsComponent - компонент о новых клиентах.
 */
class TrainingProgramsComponent extends \CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'training_programs';
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
    /** @var int Общее количество */
    private $totalCount;
    /** @var array Подразделения */
    private $divisions;

    private $attempts;
    private $courseTestsAll;
    private $arrCourses;
    private $arrTests;

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
        if (Loader::includeModule("onizamov.reports")) {
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
        $this->divisions = $this->getDivisions();
        $this->attempts = $this->getAttempts();
        $courses = $this->geCourseTests();

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['HEADERS'] = [
            [
                'id'      => User::DIVISION_CODE,
                'name'    => User::DIVISION_NAME,
                'default' => true,
                'sort'    => false,
                'filter'  => true,
                'type'    => 'dest_selector',
                'params'  => [
                    'apiVersion'              => 2,
                    'context'                 => 'USER_LIST_FILTER_DEPARTMENT',
                    'multiple'                => 'Y',
                    'contextCode'             => 'DR',
                    'enableDepartments'       => 'Y',
                    'departmentFlatEnable'    => 'Y',
                    'enableAll'               => 'N',
                    'enableUsers'             => 'N',
                    'enableSonetgroups'       => 'N',
                    'allowEmailInvitation'    => 'N',
                    'departmentSelectDisable' => 'N',
                    'isNumeric'               => 'N',
                ],
            ],

            [
                'id'     => User::USER_ID_CODE,
                'name'   => User::USER_ID_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'dest_selector',
                'params' => [
                    'enableUsers'     => 'Y',
                    'allowUserSearch' => 'N',
                    'context'         => 'CRM',
                    'contextCode'     => 'CRM',
                    'multiple'        => 'Y',
                    'enableCrm'       => 'N',
                    'enableCrmDeals'  => 'N',
                ],
                'prefix' => '',
            ],

            [
                'id'     => User::POST_CODE,
                'name'   => User::POST_NAME,
                'sort'   => false,
                'filter' => false,
            ],
            [
                'id'     => User::EMPLOYMENT_DATE_CODE,
                'name'   => User::EMPLOYMENT_DATE_NAME,
                'sort'   => false,
                'filter' => false,
            ],
            [
                'id'     => Gradebook::ATTEMPTS_CODE,
                'name'   => Gradebook::ATTEMPTS_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'number',
            ],
            [
                'id'     => Gradebook::PASSING_TEST_DATE_CODE,
                'name'   => Gradebook::PASSING_TEST_DATE_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => RequestDeal::TRAINING_NAME_CODE,
                'name'   => RequestDeal::TRAINING_NAME_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'dest_selector',
                'params' => [
                    'enableUsers'     => 'N',
                    'allowUserSearch' => 'N',
                    'context'         => 'CRM',
                    'contextCode'     => 'CRM',
                    'multiple'        => 'Y',
                    'enableCrm'       => 'Y',
                    'enableCrmDeals'  => 'Y',
                ],
                'prefix' => '',
            ],
            [
                'id'     => RequestDeal::DATE_START_TRAINING_CODE,
                'name'   => RequestDeal::DATE_START_TRAINING_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => RequestDeal::PROGRAMM_TRAINING_CODE,
                'name'   => RequestDeal::PROGRAMM_TRAINING_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => $courses,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => Gradebook::TEST_RESULT_CODE,
                'name'   => Gradebook::TEST_RESULT_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => [1 => 'Нет', 2 => 'Да'],
                'params' => [
                    'multiple' => 'N',
                ],
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
        $trainingDealConnectionPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setFilterOptionFromUI($trainingDealConnectionPrepareCollection);
        $this->setPaginationSettings();
        $trainingDealConnectionPrepareCollection->countTotal(true);
        $trainingDealConnectionPrepareCollection->setOffset($this->currentOffset);
        $trainingDealConnectionPrepareCollection->setLimit($this->nPageSize);
        $this->totalCount = $trainingDealConnectionPrepareCollection->exec()->getCount();
        $trainingDealConnectionCollection = $trainingDealConnectionPrepareCollection->fetchCollection();
        foreach ($trainingDealConnectionCollection as $trainingDealConnectionObj) {
            $this->arResult['ROWS'][] = [
                'id'      => $trainingDealConnectionObj->getId(),
                'columns' => $this->getPreparedFields($trainingDealConnectionObj),
            ];
        }
        $this->setPagination();
    }

    /**
     * Метод возвращает предустановленный набор для коллекции.
     *
     *
     * @return \Onizamov\Reports\Classes\Training\EO_TrainingDealConnection_Query
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDefaultPrepareCollection(): \Bitrix\Main\ORM\Query\Query
    {
        return TrainingDealConnectionTable::query()
            ->setSelect(
                [
                    TrainingDealConnectionTable::DEAL_ID,
                    TrainingDealConnectionTable::COURSE_ID,
                    TrainingDealConnectionTable::TEST_ID,
                    TrainingDealConnectionTable::REF_FIELD_DEAL,
                    TrainingDealConnectionTable::REF_FIELD_DEAL . '.' . RequestDeal::TRAINING_NAME_CODE,
                    TrainingDealConnectionTable::REF_FIELD_DEAL . '.' . RequestDeal::DATE_START_TRAINING_CODE,
                    TrainingDealConnectionTable::REF_FIELD_DEAL . '.' . RequestDeal::PROGRAMM_TRAINING_CODE,
                    TrainingDealConnectionTable::REF_FIELD_DEAL . '.' . RequestDeal::EMPLOYEE_CODE,
                    TrainingDealConnectionTable::USER,
                    TrainingDealConnectionTable::USER . '.' . UserTable::FULL_NAME,
                    TrainingDealConnectionTable::USER . '.' . User::DIVISION_CODE,
                    TrainingDealConnectionTable::USER . '.' . User::EMPLOYMENT_DATE_CODE,
                    TrainingDealConnectionTable::GRADEBOOK . '.' . Gradebook::TEST_RESULT_CODE,
                    TrainingDealConnectionTable::GRADEBOOK . '.' . Gradebook::ATTEMPTS_CODE,
                    TrainingDealConnectionTable::GRADEBOOK . '.' . GradebookTable::PASSING_TEST_DATE,
                ]
            );
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param \Onizamov\Reports\Classes\Training\EO_TrainingDealConnection $trainingDealConnectionObj
     * @return array
     */
    private function getPreparedFields(
        \Onizamov\Reports\Classes\Training\EO_TrainingDealConnection $trainingDealConnectionObj
    ): array {
        $row = [];

        $row['ID'] = $trainingDealConnectionObj->getId();
        $testName = $this->arrTests[$trainingDealConnectionObj->get(TrainingDealConnectionTable::TEST_ID)] ?: '';
        $courseName = $this->arrCourses[$trainingDealConnectionObj->get(TrainingDealConnectionTable::COURSE_ID)] ?: '';
        $row[RequestDeal::PROGRAMM_TRAINING_CODE] = $courseName . ' - ' . $testName;;

        if ($gradeBookObj = $trainingDealConnectionObj->get(TrainingDealConnectionTable::GRADEBOOK)) {
            $row[Gradebook::TEST_RESULT_CODE] = $gradeBookObj->getCompleted() ? 'Да' : 'Нет';
            $row[Gradebook::ATTEMPTS_CODE] = $gradeBookObj->get(Gradebook::ATTEMPTS_CODE);
            $row[Gradebook::PASSING_TEST_DATE_CODE] = $gradeBookObj->get(
                GradebookTable::PASSING_TEST_DATE
            );
        }

        if ($userObj = $trainingDealConnectionObj->get(GradebookTable::USER)) {
            $row[User::DIVISION_CODE] = $this->prepareDivisionName(
                $userObj->get(User::DIVISION_CODE)
            );
            $row[User::USER_ID_CODE] = '<a href="/company/personal/user/' . $userObj->getId() . '/" >' .
                $userObj->get(UserTable::FULL_NAME) . '</a>';
            $row[User::POST_CODE] = $userObj->get(User::POST_CODE);
            $row[User::EMPLOYMENT_DATE_CODE] = $userObj->get(User::EMPLOYMENT_DATE_CODE);
        }

        if ($dealObj = $trainingDealConnectionObj->get(GradebookTable::REF_FIELD_DEAL)) {
            $row[RequestDeal::TRAINING_NAME_CODE] = '<a href="/crm/deal/details/' . $dealObj->getId() . '/" >' .
                $dealObj->get(RequestDeal::TRAINING_NAME_CODE) . '</a>';
            $row[RequestDeal::DATE_START_TRAINING_CODE] = $dealObj->get(RequestDeal::DATE_START_TRAINING_CODE);
        }

        return $row;
    }


    /**
     * Метод получения статусов.
     *
     * @return array
     */
    private function getDivisions(): array
    {
        $rsIBlock = \CIBlock::GetList([], ["CODE" => "departments"]);
        $arIBlock = $rsIBlock->Fetch();
        $iblockID = $arIBlock["ID"];

        $dbDepartment = \CIBlockSection::GetList(
            ['ID' => "ASC"],
            [
                "IBLOCK_ID" => $iblockID,
            ],
            false,
            [
                "IBLOCK_SECTION_ID",
                'NAME',
                'ID',
            ]
        );
        while ($arrDepartment = $dbDepartment->Fetch()) {
            $fields[$arrDepartment['ID']] = $arrDepartment['NAME'];
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getAttempts(): array
    {
        $fields = [];
        $attemptCollection = AttemptTable::query()
            ->setSelect(
                [
                    'STUDENT_ID',
                    'DATE_END',
                    'TEST_ID',
                    'ID',
                ]
            )
            ->setOrder(['ID' => 'ASC'])
            ->fetchCollection();

        foreach ($attemptCollection as $attemptElement) {
            if (!empty($attemptElement->getStudentId())
                && !empty($attemptElement->getTestId())
                && !empty($attemptElement->getDateEnd())) {
                [$date, $time] = explode(' ', $attemptElement->getDateEnd());
                $fields[$attemptElement->getStudentId() . '_' . $attemptElement->getTestId()] = $date;
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function geCourseTests(): array
    {
        $fields = [];
        $res = \CCourse::GetList(
            ['NAME' => 'ASC'],
            [
                "ACTIVE"            => "Y",
                "ACTIVE_DATE"       => "Y",
                "CHECK_PERMISSIONS" => 'Y',
            ]
        );
        while ($arCourse = $res->GetNext()) {
            $this->arrCourses[$arCourse['ID']] = $arCourse['NAME'];
        }

        $rsTest = \CTest::GetList(
            ["SORT" => "ASC"],
            ['ACTIVE' => 'Y']
        );
        while ($arTest = $rsTest->GetNext()) {
            if (!empty($arTest['COURSE_ID']) && !empty($arTest['ID']) && !empty($arTest['NAME'])) {
                $this->arrTests[$arTest['ID']] = $arTest['NAME'];
                $fields[$arTest['COURSE_ID'] . ' - ' . $arTest['ID']] = $this->arrCourses[$arTest['COURSE_ID']] . ' - ' . $arTest['NAME'];
                $this->courseTestsAll[$arTest['COURSE_ID'] . ' - ' . $arTest['ID']] = $this->arrCourses[$arTest['COURSE_ID']] . ' - ' . $arTest['NAME'];
            }
        }

        return $fields;
    }

    /**
     * Метод получения Результатирующей строки подразделения.
     *
     * @param array $arrDivisionId
     * @return string
     */
    private function prepareDivisionName(array $arrDivisionId): string
    {
        $arrDivision = [];
        foreach ($arrDivisionId as $divisionId) {
            $arrDivision[] = $this->divisions[$divisionId];
        }

        return implode(' , ', $arrDivision);
    }

    /**
     * Установка фильтра c UI.
     *
     * @param $companyPrepareCollection
     * @return mixed
     * @throws \Bitrix\Main\ObjectException
     */
    private function setFilterOptionFromUI(&$trainingDealConnectionObj)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)
                && ($key !== Gradebook::ATTEMPTS_CODE . '_from')
                && ($key !== Gradebook::ATTEMPTS_CODE . '_to')
            ) {
                continue;
            }
            switch ($key) {
                case User::DIVISION_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('D', '', $item);
                        }
                    );
                    $trainingDealConnectionObj->whereIn(
                        TrainingDealConnectionTable::USER . '.' . User::DIVISION_CODE,
                        $value
                    );
                    break;
                case RequestDeal::PROGRAMM_TRAINING_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            [$courseId, $testId] = explode('-', $item);
                            $item = ['COURSE_ID' => $courseId, 'TEST_ID' => $testId];
                        }
                    );
                    $trainingDealConnectionObj->whereIn(TrainingDealConnectionTable::COURSE_ID, array_column($value,'COURSE_ID'));
                    $trainingDealConnectionObj->whereIn(TrainingDealConnectionTable::TEST_ID, array_column($value,'TEST_ID'));
                    break;
                case User::USER_ID_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $trainingDealConnectionObj->whereIn(TrainingDealConnectionTable::USER . '.ID', $value);
                    break;
                case Gradebook::ATTEMPTS_CODE . '_from':
                case Gradebook::ATTEMPTS_CODE . '_to':
                    $operation = '';
                    $operationValue = '';
                    switch ($filterOps[Gradebook::ATTEMPTS_CODE . '_numsel']) {
                        case 'less':
                            $operation = '<';
                            $operationValue = $filterOps[Gradebook::ATTEMPTS_CODE . '_to'];
                            break;
                        case 'more':
                            $operation = '>';
                            $operationValue = $filterOps[Gradebook::ATTEMPTS_CODE . '_from'];
                            break;
                        case 'exact':
                            $operation = '=';
                            $operationValue = $filterOps[Gradebook::ATTEMPTS_CODE . '_from'];
                            break;
                        case 'range':
                            if (!isset($filterOps[Gradebook::ATTEMPTS_CODE . '_from'])) {
                                $operation = '<';
                                $operationValue = $filterOps[Gradebook::ATTEMPTS_CODE . '_to'];
                            } elseif (!isset($filterOps[Gradebook::ATTEMPTS_CODE . '_to'])) {
                                $operation = '>';
                                $operationValue = $filterOps[Gradebook::ATTEMPTS_CODE . '_from'];
                            } else {
                                $operation = 'between';
                            }
                            break;
                    }
                    if (!empty($operation) && in_array($operation, ['<', '>', '='])) {
                        $trainingDealConnectionObj->where(
                            TrainingDealConnectionTable::GRADEBOOK . '.' . Gradebook::ATTEMPTS_CODE,
                            $operation,
                            $operationValue
                        );
                    } elseif ($operation === 'between') {
                        $trainingDealConnectionObj->whereBetween(
                            TrainingDealConnectionTable::GRADEBOOK . '.' . Gradebook::ATTEMPTS_CODE,
                            $filterOps[Gradebook::ATTEMPTS_CODE . '_from'],
                            $filterOps[Gradebook::ATTEMPTS_CODE . '_to']
                        );
                    }
                    break;
                case Gradebook::TEST_RESULT_CODE:
                    $trainingDealConnectionObj->where(
                        TrainingDealConnectionTable::GRADEBOOK . '.' . Gradebook::TEST_RESULT_CODE,
                        $value > 1 ? 'Y' : 'N'
                    );
                    break;
                case RequestDeal::TRAINING_NAME_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('CRMDEAL', '', $item);
                        }
                    );
                    $trainingDealConnectionObj->whereIn(TrainingDealConnectionTable::DEAL_ID, $value);
                    break;
                case Gradebook::PASSING_TEST_DATE_CODE . '_from':
                    $arrStudentId = [];
                    $arrTestId = [];
                    [$dateStart, $time] = explode(' ', $filterOps[Gradebook::PASSING_TEST_DATE_CODE . '_from']);
                    [$dateEnd, $time] = explode(' ', $filterOps[Gradebook::PASSING_TEST_DATE_CODE . '_to']);
                    foreach ($this->attempts as $attemptKey => $attemptTime) {
                        if (($attemptTime >= $dateStart) && ($attemptTime <= $dateEnd)) {
                            [$arrStudentId[], $arrTestId[]] = explode('_', $attemptKey);
                        }
                    }
                    $trainingDealConnectionObj->whereIn(
                        TrainingDealConnectionTable::USER . '.ID',
                        array_unique($arrStudentId)
                    );
                    $trainingDealConnectionObj->whereIn(
                        'TEST_ID',
                        array_unique($arrTestId)
                    );
                    break;
                case RequestDeal::DATE_START_TRAINING_CODE . '_from':
                case RequestDeal::DATE_START_TRAINING_CODE . '_to':
                    $operation = (str_replace(
                            RequestDeal::DATE_START_TRAINING_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $trainingDealConnectionObj->where(
                        TrainingDealConnectionTable::REF_FIELD_DEAL . '.' . RequestDeal::DATE_START_TRAINING_CODE,
                        $operation,
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
        $this->pageObj->setRecordCount($this->totalCount);
        $this->arResult['PAGINATION'] = [
            'TOTAL'            => $this->totalCount,
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
