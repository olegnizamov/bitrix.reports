<?php

namespace Onizamov\Reports\Classes\Training;

class TrainingDealConnection extends EO_TrainingDealConnection
{
    /**
     * @var string Класс для работы с таблицей базы данных
     */
    public static $dataClass = TrainingDealConnectionTable::class;

}