<?php

namespace Onizamov\Reports\Classes\User;

class UserCollection extends EO_User_Collection
{
    /** @var string ORM класс таблицы */
    public static $dataClass = UserTable::class;
}