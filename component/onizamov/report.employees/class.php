<?php

use Bitrix\Iblock\Elements\ElementLocationsTable;
use Bitrix\Main\EO_User;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Activity\Activity;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;
use Onizamov\Reports\Classes\Crm\Document\DocumentTable;
use Onizamov\Reports\Classes\Tasks\TaskTable;
use Onizamov\Reports\Classes\Training\Gradebook;
use Onizamov\Reports\Classes\Training\GradebookTable;
use Onizamov\Reports\Classes\User\User;
use Onizamov\Reports\Classes\User\UserTable;

/**
 * Class EmployeesComponent - компонент Отчет по работникам.
 */
class EmployeesComponent extends CBitrixComponent
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'employees';

    public const DEAL_RESULT = 'UF_CRM_1619187121287';
    public const DEAL_RESULT_PROP_ID = '282';
    public const DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA = 'DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA';

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
    private $totalCount;
    /** @var array Подразделения */
    private $divisions;
    /** @var array Результаты */
    private $result;

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
        $this->divisions = $this->getDivisions();
        $this->result = $this->getResults();
        $posts = $this->getPosts();

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
                'type'   => 'list',
                'filter' => true,
                'items'  => $posts,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => User::BIRTHDAY_CODE,
                'name'   => User::BIRTHDAY_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => User::GENDER_CODE,
                'name'   => User::GENDER_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => ['M' => 'мужской', 'F' => 'женский'],
                'params' => [
                    'multiple' => 'N',
                ],
            ],
            [
                'id'   => User::SKILLS_CODE,
                'name' => User::SKILLS_NAME,
                'sort' => false,
            ],
            [
                'id'   => User::INTERESTS_CODE,
                'name' => User::INTERESTS_NAME,
                'sort' => false,
            ],
            [
                'id'     => User::EMPLOYMENT_DATE_CODE,
                'name'   => User::EMPLOYMENT_DATE_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'   => User::CORPORATE_COMPETENCIES_CODE,
                'name' => User::CORPORATE_COMPETENCIES_NAME,
                'sort' => false,
            ],
            [
                'id'   => User::PROFESSIONAL_COMPETENCIES_CODE,
                'name' => User::PROFESSIONAL_COMPETENCIES_NAME,
                'sort' => false,
            ],
            [
                'id'   => User::MANAGEMENT_COMPETENCIES_CODE,
                'name' => User::MANAGEMENT_COMPETENCIES_NAME,
                'sort' => false,
            ],
            [
                'id'     => User::END_DATE_OF_PROBATIONARY_PERIOD_CODE,
                'name'   => User::END_DATE_OF_PROBATIONARY_PERIOD_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => User::RESULT_OF_INDIVIDUAL_PLAN_CODE,
                'name'   => User::RESULT_OF_INDIVIDUAL_PLAN_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => $this->result,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => User::TASK_CODE,
                'name'   => User::TASK_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'string',
            ],
            [
                'id'     => User::START_DATE_CODE,
                'name'   => User::START_DATE_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => User::COMPLETION_DATE_CODE,
                'name'   => User::COMPLETION_DATE_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => User::TASK_EVALUATION_CODE,
                'name'   => User::TASK_EVALUATION_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => [0 => 'Не выбрано', 'N' => 'отрицательная', 'P' => 'положительная'],
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => User::TASK_STATUS_CODE,
                'name'   => User::TASK_STATUS_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => [
                    \CTasks::STATE_PENDING              => 'ждет выполнения',
                    \CTasks::STATE_IN_PROGRESS          => 'выполняется',
                    \CTasks::STATE_SUPPOSEDLY_COMPLETED => 'ждет контроля',
                    \CTasks::STATE_COMPLETED            => 'завершена',
                    \CTasks::STATE_DEFERRED            => 'отложена',
                ],
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => User::NUMBER_OF_CREATED_CONTENT_CODE,
                'name'   => User::NUMBER_OF_CREATED_CONTENT_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'number',
            ],
        ];

        $this->arResult['FILTERS'] = $this->arResult['HEADERS'];
        foreach ($this->arResult['FILTERS'] as $key => &$filter) {
            if (empty($filter['filter'])) {
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
        $usersPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setSortFromUI($usersPrepareCollection);
        $this->setFilterOptionFromUI($usersPrepareCollection);
        $this->setPaginationSettings();
        $usersPrepareCollection->countTotal(true);
        $usersPrepareCollection->setOffset($this->currentOffset);
        $usersPrepareCollection->setLimit($this->nPageSize);
        $this->totalCount = $usersPrepareCollection->exec()->getSelectedRowsCount();
        $userCollection = $usersPrepareCollection->fetchCollection();
        foreach ($userCollection as $userObj) {
            $this->arResult['ROWS'][] = [
                'id'      => $userObj->getId(),
                'columns' => $this->getPreparedFields($userObj),
            ];
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
        return UserTable::query()
            ->setSelect(
                [
                    User::ID_CODE,
                    User::DIVISION_CODE,
                    UserTable::FULL_NAME,
                    User::POST_CODE,
                    User::BIRTHDAY_CODE,
                    User::GENDER_CODE,
                    User::SKILLS_CODE,
                    User::INTERESTS_CODE,
                    User::EMPLOYMENT_DATE_CODE,
                    User::CORPORATE_COMPETENCIES_CODE,
                    User::PROFESSIONAL_COMPETENCIES_CODE,
                    User::MANAGEMENT_COMPETENCIES_CODE,
                    UserTable::ADAPTATION_PROPERTY,
                    UserTable::ADAPTATION . '.' . self::DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA,
                    UserTable::DEAL_ID,
                    UserTable::DEAL . '.' . self::DEAL_RESULT,
                    UserTable::TASKS,
                    UserTable::COUNT_BLOG_POSTS,
                ]
            )
            ->whereNotNull(User::DIVISION_CODE)
            ->whereNotNull(UserTable::FULL_NAME)
            ->whereNotNull(User::POST_CODE)
            ->whereNotNull(User::BIRTHDAY_CODE)
            ->whereNotNull(User::GENDER_CODE)
            ->whereNotNull(User::SKILLS_CODE)
            ->whereNotNull(User::INTERESTS_CODE)
            ->whereNotNull(User::EMPLOYMENT_DATE_CODE)
            ->whereNotNull(User::CORPORATE_COMPETENCIES_CODE)
            ->whereNotNull(User::PROFESSIONAL_COMPETENCIES_CODE)
            ->whereNotNull(User::MANAGEMENT_COMPETENCIES_CODE);
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param EO_User $userObj
     * @return array
     */
    private function getPreparedFields(EO_User $userObj): array
    {
        $row = [];

        $row[User::USER_ID_CODE] = '<a href="/company/personal/user/' . $userObj->getId() . '/" >' .
            $userObj->get(UserTable::FULL_NAME) . '</a>';

        $row[User::DIVISION_CODE] = $this->prepareDivisionName(
            $userObj->get(User::DIVISION_CODE)
        );
        $row[User::POST_CODE] = $userObj->get(User::POST_CODE);
        $row[User::BIRTHDAY_CODE] = $userObj->get(User::BIRTHDAY_CODE);
        if (!empty($userObj->get(User::GENDER_CODE))) {
            $row[User::GENDER_CODE] = $userObj->get(User::GENDER_CODE) == 'M' ? 'мужской' : 'женский';
        }
        $row[User::SKILLS_CODE] = $userObj->get(User::SKILLS_CODE);
        $row[User::INTERESTS_CODE] = $userObj->get(User::INTERESTS_CODE);
        $row[User::EMPLOYMENT_DATE_CODE] = $userObj->get(User::EMPLOYMENT_DATE_CODE);
        $row[User::CORPORATE_COMPETENCIES_CODE] = $userObj->get(User::CORPORATE_COMPETENCIES_CODE);
        $row[User::PROFESSIONAL_COMPETENCIES_CODE] = $userObj->get(User::PROFESSIONAL_COMPETENCIES_CODE);
        $row[User::MANAGEMENT_COMPETENCIES_CODE] = $userObj->get(User::MANAGEMENT_COMPETENCIES_CODE);

        if ($adaptationObj = $userObj->get(UserTable::ADAPTATION)) {
            $row[User::END_DATE_OF_PROBATIONARY_PERIOD_CODE] = $adaptationObj->get(
                self::DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA
            )->getValue();
        }

        if ($dealObj = $userObj->get(UserTable::DEAL)) {
            $row[User::RESULT_OF_INDIVIDUAL_PLAN_CODE] = $this->result[$dealObj->get(self::DEAL_RESULT)];
        }

        if ($taskCollection = $userObj->get(UserTable::TASKS)) {
            $countTask = 0;
            foreach ($taskCollection as $taskObj) {
                $countTask++;

                //ORM считает общее количество записей(каждая приявязка к задаче). Поэтому мы вычитаем каждую задачу из общего списка.
                $row[User::TASK_CODE] .= 'Задача ' . $taskObj->getId(
                    ) . ' - <a href="/company/personal/user/' . $userObj->getId(
                    ) . '/tasks/task/view/' . $taskObj->getId() . '/">' .
                    $taskObj->get(TaskTable::TASK_TITLE) . '</a><br>';

                if (!empty($taskObj->get(TaskTable::TASK_DATE_START))) {
                    $row[User::START_DATE_CODE] .= 'Задача ' . $taskObj->getId() . ' - начата в ' .
                        $taskObj->get(TaskTable::TASK_DATE_START) . '<br>';
                }

                if (!empty($taskObj->get(TaskTable::TASK_COMPLETION_DATE))) {
                    $row[User::COMPLETION_DATE_CODE] .= 'Задача ' . $taskObj->getId() . ' -  закончена в ' .
                        $taskObj->get(TaskTable::TASK_COMPLETION_DATE) . '<br>';
                }

                if (!empty($taskObj->get(TaskTable::TASK_MARK))) {
                    $row[User::TASK_EVALUATION_CODE] .= 'Задача ' . $taskObj->getId() . '- ' .
                        ($taskObj->get(TaskTable::TASK_MARK) == 'P' ? 'Положительная' : 'Отрицательная') . '<br>';
                }

                if (!empty($taskObj->get(TaskTable::TASK_STATUS))) {
                    $status = '';
                    switch ($taskObj->get(TaskTable::TASK_STATUS)) {
                        case \CTasks::STATE_PENDING:
                            $status = 'ждет выполнения';
                            break;
                        case \CTasks::STATE_IN_PROGRESS:
                            $status = 'выполняется';
                            break;
                        case \CTasks::STATE_SUPPOSEDLY_COMPLETED:
                            $status = 'ждет контроля';
                            break;
                        case \CTasks::STATE_COMPLETED:
                            $status = 'завершена';
                            break;
                        case \CTasks::STATE_DEFERRED:
                            $status = 'отложена';
                            break;
                        case \CTasks::STATE_DECLINED:
                            $status = 'отклонена';
                            break;
                    }
                    $row[User::TASK_STATUS_CODE] .= 'Задача ' . $taskObj->getId() . ' - ' .
                        $status . '<br>';
                }
            }
            $countTask = ($countTask > 1) ? --$countTask : 0;
            $this->totalCount = $this->totalCount - $countTask;
        }
        $row[User::NUMBER_OF_CREATED_CONTENT_CODE] = $userObj->get(UserTable::COUNT_BLOG_POSTS);


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
     * Метод получения значений списка результатов в Инд.плане.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getResults(): array
    {
        $fields = [];
        $resultCollection = \Onizamov\Reports\Classes\Crm\UserField\FieldEnumTable::query()
            ->setSelect(
                [
                    'ID',
                    'VALUE',
                ]
            )
            ->where('USER_FIELD_ID', self::DEAL_RESULT_PROP_ID)
            ->fetchCollection();
        foreach ($resultCollection as $result) {
            $fields[$result->getId()] = $result->getValue();
        }

        return $fields;
    }

    /**
     * Метод получения значений списка Должность.
     *
     * @return array
     */
    private function getPosts(): array
    {
        $fields = [];
        $resultCollection = UserTable::query()
            ->setSelect(
                [
                    User::POST_CODE,
                ]
            )
            ->fetchCollection();
        foreach ($resultCollection as $result) {
            if (empty($result->get(User::POST_CODE))) {
                continue;
            }
            $fields[$result->get(User::POST_CODE)] = $result->get(User::POST_CODE);
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
     * Установка фильтра c UI.
     *
     * @param $usersPrepareCollection
     * @return mixed
     * @throws \Bitrix\Main\ObjectException
     */
    private function setFilterOptionFromUI(&$usersPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)
                && ($key !== User::NUMBER_OF_CREATED_CONTENT_CODE . '_from')
                && ($key !== User::NUMBER_OF_CREATED_CONTENT_CODE . '_to')
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
                    $usersPrepareCollection->whereIn(User::DIVISION_CODE, $value);
                    break;
                case User::USER_ID_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $usersPrepareCollection->whereIn(User::ID_CODE, $value);
                    break;
                case User::POST_CODE:
                    $usersPrepareCollection->whereIn(User::POST_CODE, $value);
                    break;
                case User::BIRTHDAY_CODE . '_from':
                case User::BIRTHDAY_CODE . '_to':
                    $operation = (str_replace(
                            User::BIRTHDAY_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $usersPrepareCollection->where(
                        User::BIRTHDAY_CODE,
                        $operation,
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case User::EMPLOYMENT_DATE_CODE . '_from':
                case User::EMPLOYMENT_DATE_CODE . '_to':
                    $operation = (str_replace(
                            User::EMPLOYMENT_DATE_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $usersPrepareCollection->where(
                        User::EMPLOYMENT_DATE_CODE,
                        $operation,
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case User::GENDER_CODE:
                    $usersPrepareCollection->where(
                        User::GENDER_CODE,
                        $value
                    );
                    break;
                case User::RESULT_OF_INDIVIDUAL_PLAN_CODE:
                    $usersPrepareCollection->whereIn(UserTable::DEAL . '.' . self::DEAL_RESULT, $value);
                    break;
                case User::NUMBER_OF_CREATED_CONTENT_CODE . '_from':
                case User::NUMBER_OF_CREATED_CONTENT_CODE . '_to':
                    $operation = '';
                    $operationValue = '';
                    switch ($filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_numsel']) {
                        case 'less':
                            $operation = '<';
                            $operationValue = $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_to'];
                            break;
                        case 'more':
                            $operation = '>';
                            $operationValue = $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_from'];
                            break;
                        case 'exact':
                            $operation = '=';
                            $operationValue = $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_from'];
                            break;
                        case 'range':
                            if (empty($filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_from'])) {
                                $operation = '<';
                                $operationValue = $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_to'];
                            } elseif (empty($filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_to'])) {
                                $operation = '>';
                                $operationValue = $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_from'];
                            } else {
                                $operation = 'between';
                            }
                            break;
                    }
                    if (!empty($operation) && in_array($operation, ['<', '>', '='])) {
                        $usersPrepareCollection->where(
                            UserTable::COUNT_BLOG_POSTS,
                            $operation,
                            $operationValue
                        );
                    } elseif ($operation === 'between') {
                        $usersPrepareCollection->whereBetween(
                            UserTable::COUNT_BLOG_POSTS,
                            $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_from'],
                            $filterOps[User::NUMBER_OF_CREATED_CONTENT_CODE . '_to']
                        );
                    }
                    break;
                case User::TASK_CODE:
                    $usersPrepareCollection->whereIn(UserTable::TASKS . '.' . TaskTable::TASK_TITLE, $value);
                    break;
                case User::START_DATE_CODE . '_from':
                case User::START_DATE_CODE . '_to':
                    $operation = (str_replace(
                            User::START_DATE_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $usersPrepareCollection->where(
                        UserTable::TASKS . '.' . TaskTable::TASK_DATE_START,
                        $operation,
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case User::COMPLETION_DATE_CODE . '_from':
                case User::COMPLETION_DATE_CODE . '_to':
                    $operation = (str_replace(
                            User::COMPLETION_DATE_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $usersPrepareCollection->where(
                        UserTable::TASKS . '.' . TaskTable::TASK_COMPLETION_DATE,
                        $operation,
                        new \Bitrix\Main\Type\DateTime($value)
                    );
                    break;
                case User::TASK_EVALUATION_CODE:
                    if (!in_array("0", $value)) {
                        $usersPrepareCollection->whereIn(UserTable::TASKS . '.' . TaskTable::TASK_MARK, $value);
                    }
                    break;
                case User::TASK_STATUS_CODE:
                    $usersPrepareCollection->whereIn(UserTable::TASKS . '.' . TaskTable::TASK_STATUS, $value);
                    break;
                case User::END_DATE_OF_PROBATIONARY_PERIOD_CODE . '_from':
                    $date = new DateTime($value);
                    $value = $date->modify('-1 day')->format('d.m.Y H:i:s');
                    $usersPrepareCollection->where(
                        UserTable::ADAPTATION . '.' . self::DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA . '.VALUE',
                        ">=",
                        ConvertDateTime($value, "YYYY-MM-DD") . " 23:59:59"
                    );
                    break;

                case User::END_DATE_OF_PROBATIONARY_PERIOD_CODE . '_to':
                    $usersPrepareCollection->where(
                        UserTable::ADAPTATION . '.' . self::DATA_OKONCHANIYA_ISPYTATELNOGO_SROKA . '.VALUE',
                        '<=',
                        ConvertDateTime($value, "YYYY-MM-DD") . " 23:59:59"
                    );
                    break;
            }
        }
    }

    /**
     * Установка сортировки элементов.
     *
     * @param $usersPrepareCollection
     */
    private function setSortFromUI(&$usersPrepareCollection): void
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
                $usersPrepareCollection->setOrder([RequestDealTable::DEAL_STATUS . '.NAME' => current($aSort['sort'])]);
                break;
        }
    }
}
