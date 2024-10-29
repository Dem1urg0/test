<?php

session_start();

// Подключаем автозагрузчик Composer
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

Use App\modules\Repositories\RBooking;

$booking = new RBooking(); //создаем новый класс booking
$booking->Book();