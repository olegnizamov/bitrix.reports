<?php

namespace Onizamov\Reports\Classes\Training;

class GradebookCollection extends EO_Gradebook_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = GradebookTable::class;
}