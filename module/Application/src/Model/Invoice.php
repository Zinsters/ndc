<?php
namespace Application\Model;

class Invoice
{
    public $id;
    public $customerId;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->customerId = !empty($data['customer_id']) ? $data['customer_id'] : null;
    }
}