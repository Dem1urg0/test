<?php

namespace App\services;

use App\traits\TSingleton;

// класс для работы с базой данных PDO
class DB
{
    use TSingleton;

    //используем трейт TSingleton для реализации паттерна Singleton

    private $config = [
        'driver' => 'mysql',
        'host' => 'mariadb',
        'db' => 'dbphp',
        'charset' => 'UTF8',
        'username' => 'root',
        'password' => 'rootroot', // конфиг подключения к базе данных
    ];

    protected $connect; //свойство для хранения соединения с базой данных

    /**
     * Метод получения соединения с базой данных
     * @return \PDO
     */
    protected function getConnection()
    {
        if (empty($this->connect)) {
            $this->connect = new \PDO(
                $this->getPrepareDsnString(),
                $this->config['username'],
                $this->config['password']
            );
            $this->connect->setAttribute(
                \PDO::ATTR_DEFAULT_FETCH_MODE,
                \PDO::FETCH_ASSOC);
        }

        return $this->connect;
    }

    /**
     * Метод подготовки строки подключения
     * @return string
     */
    protected function getPrepareDsnString()
    {
        return sprintf(
            '%s:host=%s;dbname=%s;charset=%s',
            $this->config['driver'],
            $this->config['host'],
            $this->config['db'],
            $this->config['charset']
        );
    }

    /**
     * Метод запроса к базе данных
     * @param string $sql - строка запроса
     * @param array $params - параметры запроса
     * @return \PDOStatement
     * Выхывает метод prepare() для подготовки запроса и execute() для выполнения запроса
     */
    protected function query($sql, $params = [])
    {
        $PDOStatement = $this->getConnection()->prepare($sql);
        $PDOStatement->execute($params);
        return $PDOStatement;
    }

    /**
     * Метод запроса к базе данных с возвратом объекта/ов
     * @param string $sql - строка запроса
     * @param string $class - имя класса
     * @param array $params - параметры запроса
     * @return mixed
     * Возвращает объект
     */
    public function queryObject(string $sql, $class, $params = [])
    {
        $PDOStatement = $this->query($sql, $params);
        $PDOStatement->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $PDOStatement->fetch();
    }

    public function queryObjects(string $sql, $class, $params = [])
    {
        $PDOStatement = $this->query($sql, $params);
        $PDOStatement->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $PDOStatement->fetchAll();
    }

    /**
     * Метод получения одной записи из таблицы
     * @param string $sql - строка запроса
     * @param array $params - параметры запроса
     * @return array
     * Возвращает одну запись из таблицы
     */
    public function find(string $sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Метод получения всех записей из таблицы
     * @param string $sql - строка запроса
     * @param array $params - параметры запроса
     * @return array
     * Возвращает все записи из таблицы
     */
    public function findAll(string $sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Метод выполнения запроса к базе данных
     * @param string $sql - строка запроса
     * @param array $params - параметры запроса
     * Выполняет запрос к базе данных
     */
    public function exec(string $sql, $params = [])
    {
        $this->query($sql, $params);
    }
}