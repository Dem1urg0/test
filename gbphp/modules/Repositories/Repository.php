<?php

namespace App\modules\Repositories;

use App\services\DB;
use \App\modules\Entities\Model;
// абстрактный класс репозитория
abstract class Repository
{
    protected $bd;
    protected $model;
    /**
     * Конструктор репозитория
     * Принимает сущность и записывает ее в свойство model
     * Создает объект класса DB и записывает его в свойство bd
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->bd = DB::getInstance();
        $this->model = $model;

    }
    /**
     * Метод получения названия таблицы
     * @return string
     * Возвращает название таблицы
     */
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    /**
     * Метод получения одной записи из таблицы
     * @return array
     * Получает одну запись из таблицы по ключу и значению
     */
    public function getOne($id, $key = 'id')
    {
        $tableName = $this->model->getTableName();
        $sql = "SELECT * FROM {$tableName} WHERE $key = :{$key}";
        return $this->bd->queryObject($sql, get_class($this->model), [":$key" => $id]);
    }
    /**
     * Метод сохранения записи в таблицу
     * @return array
     * Проверяется есть ли запись в таблице, если нет, то вызывается метод insert
     * если есть, то возвращается сообщение об ошибке
     */
    public function save($key = 'id')
    {
        $id = $this->model->$key;
            if ($this->getOne($id, $key)) {
                return 'Такой уже существует';
            } else {
                $this->insert();
            }
    }
    /**
     * Метод добавления записи в таблицу
     * @return void
     * Получается название таблицы, имена и значения полей, и записывает их в таблицу
     */
    private function insert()
    {
        $tableName = $this->getTableName();
        $sql = "INSERT INTO {$tableName} ({$this->getParams('names')}) VALUES ({$this->getParams('values')})";
        $this->bd->exec($sql, $this->getParams('params'));
    }
    /**
     * Метод обработки параметров для PDO
     * возражает нужный формат параметров для PDO по запросу
     * @param string $name - нужный формат параметров
     * @param array $filter - массив параметров, которые не нужно обрабатывать
     * Параметры берутся из модели $params
     */
    private function getParams($name, $filter = [])
    {
        $paramsReq = [];
        foreach ($this->model as $param => $value) {
            if ($this->model->checkParams($param) || in_array($param, $filter)) {
                continue;
            }
            switch ($name) {
                case 'names':
                    $paramsReq[] = $param;
                    break;
                case 'values':
                    $paramsReq[] = ':' . $param;
                    break;
                case 'params':
                    $paramsReq[':' . $param] = $value;
                    break;
                case 'equality':
                    $paramsReq[] = $param . ' = :' . $param;
                    break;
            }
        }
        switch ($name) {
            case 'names':
            case 'values':
            case 'equality':
                return implode(', ', $paramsReq);
            case 'params':
                return $paramsReq;
            default:
                return null;
        }
    }
}
