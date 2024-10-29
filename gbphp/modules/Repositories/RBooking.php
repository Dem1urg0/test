<?php
/*
 * Репозиторий Booking - класс методов записи заказов и бронирования
*/
namespace App\modules\Repositories;

use App\modules\Entities\MBooking;

class RBooking extends Repository
{
    /**
     * Конструктор класса
     * @return void
     * @param MBooking $model - Сущность заказа
     * Отправляем в родительский класс сущность заказа для дальнейшей обработки его данных
     */
    public function __construct()
    {
        parent::__construct(new MBooking());
    }
    /**
     * Метод API - отправка данных на сервер
     * @return array
     * @param string $url - адрес сервера
     * @param array $data - данные для отправки
     * Отправляем данные на сервер и получаем ответ, в нашем случае это замоканный массив с ключами status и message
     */
    public function API($url, $data = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        // $response = curl_exec($ch);

        if ($url == 'https://api.site.com/book') {
            $mockResponse = [
                ['status' => 'success', 'message' => 'order successfully booked'],
                ['status' => 'error', 'message' => 'barcode already exists']
            ];
        } elseif ($url == 'https://api.site.com/approve') {
            $mockResponse = [
                ['status' => 'success', 'message' => 'order successfully aproved'],
                ['status' => 'error', 'message' => 'event cancelled'],
                ['status' => 'error', 'message' => 'no tickets'],
                ['status' => 'error', 'message' => 'no seats'],
                ['status' => 'error', 'message' => 'fan removed'],
            ];
        }
        $response = $mockResponse[array_rand($mockResponse)];
        curl_close($ch);
        return $response;
    }
    /**
     * Метод генерации barcode
     * @return void
     * Генерируем barcode на основе данных заказа и случайного числа, через хэш sha256
     * Записывает в модель (сушность заказа(MBooking)) barcode
     */
    public function GenBarcode()
    {
        $dataString = random_int(10000, 99999) . implode('', $this->getOrderData());
        $this->model->barcode = substr(preg_replace('/[^0-9]/', '', hash('sha256', $dataString)), 0, 12);
    }
    /**
     * Метод бронирования заказа
     * @return string
     * @param int $attempts - количество попыток бронирования
     * @param int $maxAttempts - максимальное количество попыток бронирования
     * Пытаемся забронировать заказ, максимальное количество попыток 5
     */
    public function Book($attempts = 0)
    {
        $this->GenBarcode();
        $maxAttempts = 5;
        $response = $this->API('https://api.site.com/book', $this->getOrderData());

        if ($response['status'] == 'success') {
            $this->model->setEqualPrice();
            return $this->Approve();
        } elseif ($attempts < $maxAttempts) {
            return $this->Book($attempts + 1);
        } else {
            return 'Failed to book';
        }
    }
    /**
     * Метод подтверждения заказа
     * @return string
     * Отправляем запрос на сервер для подтверждения заказа
     * Если заказ подтвержден, то вызываем метод save() для сохранения заказа в базе данных
     * Елси заказ не подтвержден, то возвращаем сообщение об ошибке
     */
    public function Approve()
    {
        $response = $this->API('https://api.site.com/approve', ['barcode' => $this->model->barcode]);
        if ($response['status'] == 'success') {
            $this->save('barcode');
        }
        return $response['message'];
    }
    /**
     * Метод получения данных заказа
     * @return array
     * Получаем данные заказа, исключая из них ненужные поля
     * Необходимо для генерации barcode и отправки данных на сервер для бронирования
     */
    public function getOrderData()
    {
        $result = array_filter((array)$this->model, function ($key) {
            return !in_array($key, ['tableName', 'user_id', 'barcode', 'equal_price']);
        }, ARRAY_FILTER_USE_KEY);
        return $result;
    }
}