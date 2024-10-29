<?php

namespace App\modules\Entities;
// абстрактный класс сущности
class Model
{
    /**
     * Метод проверки параметров
     * @return bool
     */
    public function checkParams($param)
    {
        if ($param == 'bd' || $param == 'params' || $param == 'model') {
            return true;
        } else {
            return false;
        }
    }
}