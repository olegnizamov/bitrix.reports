<?php

namespace Onizamov\Components;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Grid\Panel\Snippet;
use \Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Onizamov\Reports\Classes\Crm\Company\Company;
use Onizamov\Reports\Classes\Crm\Company\CompanyTable;
use Onizamov\Reports\Classes\User\User;
use Onizamov\Reports\Classes\User\UserTable;
use Onizamov\Reports\Classes\Vote\EventAnswerTable;
use Onizamov\Reports\Classes\Vote\Vote;

/**
 * Class QuestionnairesAndSurveysComponent - Отчет по анкетированию и опросам
 */
class QuestionnairesAndSurveysComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    /** @const Уникальный идентификатор грида */
    public const GRID_ID = 'questionnaires_surveys';
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
            && Loader::includeModule("onizamov.reports")
            && Loader::includeModule("vote")
        ) {
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
        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->divisions = $this->getDivisions();
        $survey = $this->getSurvey();
        $question = $this->getQuestion();
        $this->arResult['HEADERS'] = [
            [
                'id'   => 'ID',
                'name' => 'ID',
                'sort' => false,
            ],
            [
                'id'     => Vote::SURVEY_CODE,
                'name'   => Vote::SURVEY_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => $survey,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
            [
                'id'     => Vote::QUESTION_CODE,
                'name'   => Vote::QUESTION_NAME,
                'sort'   => false,
                'type'   => 'list',
                'filter' => true,
                'items'  => $question,
                'params' => [
                    'multiple' => 'Y',
                ],
            ],
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
                'id'     => Vote::FILLING_DATE_CODE,
                'name'   => Vote::FILLING_DATE_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'date',
            ],
            [
                'id'     => Vote::RESULTS_CODE,
                'name'   => Vote::RESULTS_NAME,
                'sort'   => false,
                'filter' => false,
            ],
            [
                'id'     => Vote::COUNT_ANSWERS_CODE,
                'name'   => Vote::COUNT_ANSWERS_NAME,
                'sort'   => false,
                'filter' => true,
                'type'   => 'number',
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

    /**455
     * Метод получения опросов.
     *
     * @return array
     */
    private function getSurvey(): array
    {
        $result = [];
        $collection = \Bitrix\Vote\ChannelTable::query()
            ->setSelect(
                [
                    'ID',
                    'TITLE',
                ]
            )->fetchCollection();
        foreach ($collection as $obj) {
            $result[$obj->getId()] = $obj->getTitle();
        }
        return $result;
    }

    /**
     * Метод получения вопросов.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getQuestion(): array
    {
        $result = [];
        $collection = \Bitrix\Vote\QuestionTable::query()
            ->setSelect(
                [
                    'ID',
                    'QUESTION',
                ]
            )->fetchCollection();
        foreach ($collection as $obj) {
            $result[$obj->getId()] = $obj->getQuestion();
        }
        return $result;
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
        $eventAnswersPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setFilterOptionFromUI($eventAnswersPrepareCollection);
        $this->setPaginationSettings();
        $eventAnswersPrepareCollection->countTotal(true);
        $eventAnswersPrepareCollection->setOffset($this->currentOffset);
        $eventAnswersPrepareCollection->setLimit($this->nPageSize);
        $this->totalCount = $eventAnswersPrepareCollection->exec()->getCount();
        $eventAnswersCollection = $eventAnswersPrepareCollection->fetchCollection();
        $this->arResult['DIAGRAMMS'] = [];

        foreach ($eventAnswersCollection as $eventAnswersObj) {
            $result = $this->getPreparedFields($eventAnswersObj);
            $this->arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']][$result['RESULTS']]['COUNT']++;
            $this->arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']]['ALL']++;
            $this->arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']][$result['RESULTS']]['COLOR'] = '#' . str_pad(
                    dechex(mt_rand(0, 0xFFFFFF)),
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $this->arResult['ROWS'][] = [
                'id'      => $eventAnswersObj->getId(),
                'columns' => $result,
            ];
        }
        $this->setPagination();
    }

    /**
     * Метод возвращает предустановленный набор для коллекции.
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDefaultPrepareCollection(): \Bitrix\Main\ORM\Query\Query
    {
        return EventAnswerTable::query()
            ->setSelect(
                [
                    EventAnswerTable::ID,
                    EventAnswerTable::EVENT_QUESTION_ID,
                    EventAnswerTable::MESSAGE,
                    EventAnswerTable::EVENT_QUESTION,
                    EventAnswerTable::ANSWER,
                    EventAnswerTable::EVENT,
                    EventAnswerTable::USER,
                    EventAnswerTable::COUNT_ANSWERS,
                    EventAnswerTable::QUESTION,
                    EventAnswerTable::VOTE,
                    EventAnswerTable::USER . '.' . UserTable::FULL_NAME,
                    EventAnswerTable::USER . '.' . User::DIVISION_CODE,
                ]
            );
    }

    /**
     * Метод получения предподготовленных данных для объекта сделки.
     *
     * @param \Onizamov\Reports\Classes\Vote\Vote $eventAnswersObj
     * @return array
     */
    private function getPreparedFields(\Onizamov\Reports\Classes\Vote\Vote $eventAnswersObj): array
    {
        $row = [];

        $row['ID'] = $eventAnswersObj->getId();
        if ($eventObj = $eventAnswersObj->get(EventAnswerTable::EVENT)) {
            [$date, $time] = explode(' ', $eventObj->get('DATE_VOTE'));
            $row[Vote::FILLING_DATE_CODE] = $date;
        }

        if ($userObj = $eventAnswersObj->get(EventAnswerTable::USER)) {
            $row[User::DIVISION_CODE] = $this->prepareDivisionName(
                $userObj->get(User::DIVISION_CODE)
            );
            $row[User::USER_ID_CODE] = '<a href="/company/personal/user/' . $userObj->getId() . '/" >' .
                $userObj->get(UserTable::FULL_NAME) . '</a>';
            $row[User::POST_CODE] = $userObj->get(User::POST_CODE);
        }

        $row[Vote::COUNT_ANSWERS_CODE] = $eventAnswersObj->get(EventAnswerTable::COUNT_ANSWERS);

        if ($questionObj = $eventAnswersObj->get(EventAnswerTable::QUESTION)) {
            $row[Vote::QUESTION_CODE] = $questionObj->get('QUESTION');
        }

        if ($voteObj = $eventAnswersObj->get(EventAnswerTable::VOTE)) {
            $row[Vote::SURVEY_CODE] = $voteObj->get('TITLE');
        }

        if ($answerObj = $eventAnswersObj->get(EventAnswerTable::ANSWER)) {
            $answerAnother = $eventAnswersObj->get('MESSAGE');
            $row[Vote::RESULTS_CODE] = $answerAnother ?: $answerObj->get('MESSAGE');
        }

        return $row;
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
    private function setFilterOptionFromUI(&$eventAnswersPrepareCollection)
    {
        $filterOptions = new  Options($this->arResult["GRID_ID"]);
        $filterOps = $filterOptions->getFilter();
        foreach ($filterOps as $key => $value) {
            if (empty($value)
                && ($key !== Vote::COUNT_ANSWERS_CODE . '_from')
                && ($key !== Vote::COUNT_ANSWERS_CODE . '_to')
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
                    $eventAnswersPrepareCollection->whereIn(EventAnswerTable::USER . '.' . User::DIVISION_CODE, $value);
                    break;
                case User::USER_ID_CODE:
                    array_walk(
                        $value,
                        function (&$item) {
                            $item = str_replace('U', '', $item);
                        }
                    );
                    $eventAnswersPrepareCollection->whereIn(EventAnswerTable::USER . '.ID', $value);
                    break;
                case Vote::SURVEY_CODE:
                    $eventAnswersPrepareCollection->whereIn(EventAnswerTable::VOTE . '.ID', $value);
                    break;
                case Vote::QUESTION_CODE:
                    $eventAnswersPrepareCollection->whereIn(EventAnswerTable::EVENT_QUESTION . '.QUESTION_ID', $value);
                    break;
                case Vote::COUNT_ANSWERS_CODE . '_from':
                case Vote::COUNT_ANSWERS_CODE . '_to':
                    $operation = '';
                    $operationValue = '';
                    switch ($filterOps[Vote::COUNT_ANSWERS_CODE . '_numsel']) {
                        case 'less':
                            $operation = '<';
                            $operationValue = $filterOps[Vote::COUNT_ANSWERS_CODE . '_to'];
                            break;
                        case 'more':
                            $operation = '>';
                            $operationValue = $filterOps[Vote::COUNT_ANSWERS_CODE . '_from'];
                            break;
                        case 'exact':
                            $operation = '=';
                            $operationValue = $filterOps[Vote::COUNT_ANSWERS_CODE . '_from'];
                            break;
                        case 'range':
                            if (!isset($filterOps[Vote::COUNT_ANSWERS_CODE . '_from'])) {
                                $operation = '<';
                                $operationValue = $filterOps[Vote::COUNT_ANSWERS_CODE . '_to'];
                            } elseif (!isset($filterOps[Vote::COUNT_ANSWERS_CODE . '_to'])) {
                                $operation = '>';
                                $operationValue = $filterOps[Vote::COUNT_ANSWERS_CODE . '_from'];
                            } else {
                                $operation = 'between';
                            }
                            break;
                    }
                    if (!empty($operation) && in_array($operation, ['<', '>', '='])) {
                        $eventAnswersPrepareCollection->where(
                            EventAnswerTable::COUNT_ANSWERS,
                            $operation,
                            $operationValue
                        );
                    } elseif ($operation === 'between') {
                        $eventAnswersPrepareCollection->whereBetween(
                            EventAnswerTable::COUNT_ANSWERS,
                            $filterOps[Vote::COUNT_ANSWERS_CODE . '_from'],
                            $filterOps[Vote::COUNT_ANSWERS_CODE . '_to']
                        );
                    }
                    break;
                case Vote::FILLING_DATE_CODE . '_from':
                case Vote::FILLING_DATE_CODE . '_to':
                    $operation = (str_replace(
                            Vote::FILLING_DATE_CODE,
                            '',
                            $key
                        ) == '_from') ? ">=" : "<=";
                    $eventAnswersPrepareCollection->where(
                        EventAnswerTable::EVENT . '.DATE_VOTE',
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

    /**
     * Метод определения AJAX запросов
     */
    public function configureActions()
    {
        return [
            'getDiagrams' => [
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
    public function getDiagramsAction()
    {
        Loader::includeModule("onizamov.reports");

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $eventAnswersPrepareCollection = $this->getDefaultPrepareCollection();
        $this->setFilterOptionFromUI($eventAnswersPrepareCollection);
        $eventAnswersPrepareCollection->setOffset($this->currentOffset);
        $eventAnswersPrepareCollection->setLimit($this->nPageSize);
        $eventAnswersCollection = $eventAnswersPrepareCollection->fetchCollection();
        $arResult['DIAGRAMMS'] = [];

        foreach ($eventAnswersCollection as $eventAnswersObj) {
            $result = $this->getPreparedFields($eventAnswersObj);
            $arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']][$result['RESULTS']]['COUNT']++;
            $arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']]['ALL']++;
            $arResult['DIAGRAMMS'][$result['SURVEY']][$result['QUESTION']][$result['RESULTS']]['COLOR'] = '#' . str_pad(
                    dechex(mt_rand(0, 0xFFFFFF)),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        }
        $html = $this->prepareHtmlView($arResult['DIAGRAMMS']);
        return ['HTML' => $html];
    }

    /**
     * Метод получения Результатирующей строки подразделения.
     *
     * @param array $arrDivisionId
     * @return string
     */
    private function prepareHtmlView(array $arDiagrams): string
    {
        ob_start();
        ?>
        <div class="voting-result-box">
            <ol class="vote-items-list voting-list-box">
                <?
                foreach ($arDiagrams as $keySurvey => $arrSurvey) { ?>
                    <li class="vote-item-vote vote-item-vote-first vote-item-vote-last vote-item-vote-odd">
                        <div class="vote-item-header">
                            <span class="vote-item-title"><?= $keySurvey; ?></span>
                            <div class="vote-clear-float"></div>
                        </div>
                        <ol class="vote-items-list vote-question-list">
                            <?
                            foreach (
                                $arrSurvey
                                as $keyQuestion => $arQuestion
                            ) {
                                ?>
                                <li class="vote-question-item">
                                    <div class="vote-item-header">
                                        <div class="vote-item-title vote-item-question"><?= $keyQuestion; ?></div>
                                        <div class="vote-clear-float"></div>
                                    </div>
                                    <table class="vote-answer-table">
                                        <?
                                        foreach ($arQuestion as $key => $arAnswer) {
                                            if ($key == 'ALL') {
                                                continue;
                                            }
                                            ?>
                                            <tr class='vote-answer-row'>
                                                <td width="30%">
                                                    <?= $key ?>
                                                </td>
                                                <td width="70%">
                                                    <table class="vote-bar-table">
                                                        <tr>
                                                            <?
                                                            $percent = round(
                                                                $arAnswer["COUNT"] / $arQuestion['ALL'] * 100
                                                            );
                                                            ?>
                                                            <td>
                                                                <div style="height:18px;float:left;width:<?= 0.8 * $percent ?>%;background-color: <?= $arAnswer["COLOR"] ?>;"></div>
                                                                <span style="line-height:18px;width:20%;float:left;"
                                                                      class="answer-counter"><nobr>&nbsp;<?= $arAnswer['COUNT'] ?> (<?= $percent ?>%)</nobr></span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?
                                        } ?>
                                    </table>
                                </li>
                                <?
                            } ?>
                        </ol>
                    </li>
                    <?
                } ?>
            </ol>
        </div>
        <?php
        $page = ob_get_contents();
        ob_end_clean();
        return $page;
    }

}
