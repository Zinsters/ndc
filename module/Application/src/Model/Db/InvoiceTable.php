<?php
namespace Application\Model\Db;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class InvoiceTable extends AbstractTable
{
    public function getCustomerInvoices($customerId)
    {
        $result = $this->_tableGateway->select( ['customer_id' => $customerId] );
	    
	    return $result;
    }
}