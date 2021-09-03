<?php

namespace Onizamov\Reports\Classes\Crm\Document;

use Bitrix\Crm\DealTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Onizamov\Reports\Classes\Crm\Deal\RequestDeal;
use Onizamov\Reports\Classes\Crm\Deal\RequestDealTable;

/**
 * Class RequestDealTable Описание таблицы для работы с сущностью сделки - Запрос.
 */
class DocumentTable extends \Bitrix\DocumentGenerator\Model\DocumentTable
{
    /** @var string Запрос передан */
    public const DEAL = 'DEAL';

    /** @var string Запрос передан */
    public const DEAL_PROVIDER = 'bitrix\crm\integration\documentgenerator\dataprovider\deal';

    /**
     * Кастомный класс Обекта EO_..
     *
     * @return string
     */
    public static function getObjectClass()
    {
        return Document::class;
    }

    /**
     * Кастомный класс коллекции.
     *
     * @return string
     */
    public static function getCollectionClass()
    {
        return DocumentCollection::class;
    }

    /**
     * Добавление дополнительных полей.
     *
     * @return array
     * @throws ObjectPropertyException
     * @throws SystemException
     *
     * @throws ArgumentException
     */
    public static function getMap()
    {
        $map = parent::getMap();

        return array_merge(
            $map,
            [
                self::DEAL => new Reference(
                    self::DEAL,
                    RequestDealTable::getEntity(),
                    Join::on('ref.ID', 'this.' . Document::ENTITY_ID_CODE)
                ),
            ]
        );
    }


    /**
     * Событие добавление документа
     * @param \Bitrix\Main\ORM\Event $event
     */
    public static function onAfterAdd($event)
    {
        $entityProvider = $event->getParameter('fields')['PROVIDER'];
        if ($entityProvider !== self::DEAL_PROVIDER) {
            return;
        }
        $entityId = $event->getParameter('fields')['VALUE'];
        $documentNumber = $event->getParameter('fields')['NUMBER'];

        /** Проверка, что данный тип поля - Запросы простых и архитектурных продаж. */
        $dealsObj = DealTable::query()
            ->where('CATEGORY_ID', RequestDeal::CATEGORY_ID_REQUEST)
            ->where('ID', $entityId)
            ->fetchObject();

        if (!empty($dealsObj)) {
            /** Обновление сделки - поля Номер КП */
            $deal = new \CCrmDeal();
            $arFields = [RequestDeal::NUMBER_COMMERCIAL_PROPOSAL_CODE => $documentNumber];
            $deal->Update(
                $entityId,
                $arFields,
                true,
                true,
                ['DISABLE_USER_FIELD_CHECK' => true]
            );
        }
    }
}
