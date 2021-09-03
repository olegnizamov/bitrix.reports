<?php

namespace Onizamov\Reports\Classes\Crm\Deal;

use Bitrix\Crm\DealTable;
use Onizamov\Reports\Classes\Training\TrainingDealConnectionTable;

class Deal
{
    /** @var int Категория сделки - Заявки */
    public const CATEGORY_ID_REQUEST = 2;
    public const CATEGORY_ID_REQUEST_UF = 'UF_CRM_1620897335';
    /** @var int Категория сделки - Запросы простых и архитектурных продаж */
    public const CATEGORY_ID_SIMPLE_SALES = 0;
    public const CATEGORY_ID_SIMPLE_SALES_UF = 'UF_CRM_1620897321';
    /** @var int Категория сделки - Индивидуальный план */
    public const CATEGORY_ID_INDIVIDUAL_PLAN = 4;
    /** @var string Индивидуальный план- Руководитель */
    public const INDIVIDUAL_PLAN_DIRECTOR_UF = 'UF_CRM_1619769080';
    /** @var string Индивидуальный план- Куратор */
    public const INDIVIDUAL_PLAN_CURATOR_UF = 'UF_CRM_1619769097';
    public const COURSE_AND_TEST_UF = 'UF_CRM_1624460195114';


    /**
     * Событие обновление сделки
     *
     * @param array $fields
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function onUpdate(array $fields)
    {
        if (empty($fields[RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE])) {
            return;
        }
        $numberOfCommercialProposal = $fields[RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE];

        //поиск сделок с другим типом с данным номером КП
        $dealsCollection = DealTable::query()->setSelect(
            [
                'CATEGORY_ID',
                'ID',
            ]
        )
            ->where(RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE, $numberOfCommercialProposal)
            ->fetchCollection();

        $arrDealsById = [];
        $arrDealsByType = [];
        foreach ($dealsCollection as $dealObj) {
            $arrDealsById[$dealObj->getId()] = $dealObj->get('CATEGORY_ID');
            $arrDealsByType[$dealObj->get('CATEGORY_ID')][$dealObj->getId()] = (string)$dealObj->getId();
        }

        if (empty($arrDealsByType[self::CATEGORY_ID_SIMPLE_SALES]) || empty($arrDealsByType[self::CATEGORY_ID_REQUEST])) {
            return;
        }

        foreach ($arrDealsById as $dealId => $typeDeal) {
            if ($typeDeal == self::CATEGORY_ID_REQUEST) {
                $arFields = [self::CATEGORY_ID_REQUEST_UF => $arrDealsByType[self::CATEGORY_ID_SIMPLE_SALES]];
            } else {
                $arFields = [self::CATEGORY_ID_SIMPLE_SALES_UF => current($arrDealsByType[self::CATEGORY_ID_REQUEST])];
            }

            $GLOBALS['USER_FIELD_MANAGER']->Update('CRM_DEAL', $dealId, $arFields);
        }
    }


    /**
     * Событие добавления сделки сделки
     *
     * @param array $fields
     */
    public static function onAdd(array $fields)
    {
        self::onUpdate($fields);
    }

    public static function OnAfterHandlerSetConnectionDealTestAdd(&$arFields)
    {
        if (empty($arFields[self::COURSE_AND_TEST_UF])) {
            return;
        }

        $dealId = $arFields['ID'];
        foreach ($arFields[self::COURSE_AND_TEST_UF] as $courseTest) {
            [$courseName, $testName, $place, $date, $timeFrom, $timeTo, $controller] = explode(
                ' — ',
                $courseTest
            );
            $result = self::getTestIdAndCourseId($courseName, $testName);
            if (!empty($result['COURSE_ID']) && !empty($result['TEST_ID'])) {
                $isExist = TrainingDealConnectionTable::isExistRowByProperties(
                    [
                        'COURSE_ID' => $result['COURSE_ID'],
                        'TEST_ID'   => $result['TEST_ID'],
                        'DEAL_ID'   => $dealId,
                    ]
                );
                if (!$isExist) {
                    TrainingDealConnectionTable::add(
                        [
                            'COURSE_ID' => $result['COURSE_ID'],
                            'TEST_ID'   => $result['TEST_ID'],
                            'DEAL_ID'   => $dealId,
                        ]
                    );
                }
            }
        }
    }
    public static function OnAfterHandlerSetConnectionDealTestUpdate(&$arFields)
    {
        if (empty($arFields[self::COURSE_AND_TEST_UF])) {
            return;
        }
        self::OnAfterHandlerSetConnectionDealTestAdd($arFields);
        /** Удаляем записи. Кейс, когда изменен тест или курс */
        $dealId = $arFields['ID'];
        $arrIDCourses = [];
        $arrIDTests = [];
        foreach ($arFields[self::COURSE_AND_TEST_UF] as $courseTest) {
            [$courseName, $testName, $place, $date, $timeFrom, $timeTo, $controller] = explode(
                ' — ',
                $courseTest
            );
            $result = self::getTestIdAndCourseId($courseName, $testName);
            if (!empty($result['COURSE_ID']) && !empty($result['TEST_ID'])) {
                $arrIDCourses[] = $result['COURSE_ID'];
                $arrIDTests[] = $result['TEST_ID'];
            }
        }

        if (!empty($arrIDCourses) && !empty($arrIDTests)) {
            /** Если есть не совпадающие тесты */
            $rows = TrainingDealConnectionTable::getFieldsByProperties(
                ['ID', 'COURSE_ID', 'TEST_ID'],
                ['!TEST_ID' => $arrIDTests, 'DEAL_ID' => $dealId]
            );
            foreach ($rows as $id) {
                TrainingDealConnectionTable::delete($id['ID']);
            }
            /** Если есть не совпадающие курсы */
            $rows = TrainingDealConnectionTable::getFieldsByProperties(
                ['ID', 'COURSE_ID', 'TEST_ID'],
                ['!COURSE_ID' => $arrIDCourses, 'DEAL_ID' => $dealId]
            );
            foreach ($rows as $id) {
                TrainingDealConnectionTable::delete($id['ID']);
            }
        }
    }
    public static function OnBeforeHandlerSetConnectionDealTestDelete(&$dealId)
    {
        $arrIDs = TrainingDealConnectionTable::getFieldsByProperties(
            ['ID'],
            ['DEAL_ID' => $dealId]
        );
        foreach ($arrIDs as $id) {
            TrainingDealConnectionTable::delete($id['ID']);
        }
    }
    /**
     * Метод получения ID теста и ID курса
     *
     * @param string $courseName
     * @param string $testName
     * @return array
     */
    public static function getTestIdAndCourseId(string $courseName, string $testName): array
    {
        $arCourses = [];
        $result = [];

        $res = \CCourse::GetList(
            ['NAME' => 'ASC'],
            [
                "ACTIVE" => "Y",
                "NAME"   => $courseName,
            ]
        );
        while ($arCourse = $res->GetNext()) {
            $arCourses[] = $arCourse['ID'];
        }

        $res = \CTest::GetList(
            ['NAME' => 'ASC'],
            [
                "ACTIVE"     => "Y",
                "NAME"       => $testName,
                "=COURSE_ID" => $arCourses,
            ]
        );
        if ($arTest = $res->GetNext()) {
            $result = ['COURSE_ID' => $arTest['COURSE_ID'], 'TEST_ID' => $arTest['ID']];
        }
        return $result;
    }


}
