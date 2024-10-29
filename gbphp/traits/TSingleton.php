<?php

namespace App\traits;
// трейт для реализации паттерна Singleton
trait TSingleton
{
    protected function __construct() {}
    protected function __clone() {}
    public function __wakeup() {}

    private static $items;

    public static function getInstance()
    {
        if (empty(static::$items)) {
            static::$items = new static();
        }

        return static::$items;
    }
}