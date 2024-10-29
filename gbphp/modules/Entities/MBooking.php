<?php
//Сущность бронирования
namespace App\modules\Entities;

class MBooking extends Model
{
    public $event_id = '002';
    public $event_date = '2021-12-31 16:00:00';
    public $ticket_adult_price = '200';
    public $ticket_adult_quantity = '3';
    public $ticket_kid_price = '550';
    public $ticket_kid_quantity = '3';
    public $barcode = 'none';
    public $user_id = '00751';
    public $equal_price = '0';
    /**
     * Метод получения названия таблицы
     * @return string
     * Возвращает название таблицы
     */
    public function getTableName()
    {
        return 'booking';
    }
    /**
     * Метод расчета общей стоимости заказа
     * Записывает общую стоимость заказа в свойство equal_price
     */
    public function setEqualPrice(){
        $this->equal_price = ($this->ticket_adult_price * $this->ticket_adult_quantity)
            + ($this->ticket_kid_price * $this->ticket_kid_quantity);
    }
}