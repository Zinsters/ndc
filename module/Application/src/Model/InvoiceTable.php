<?php
namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class InvoiceTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function getCustomerInvoices($customerId)
    {
        $result = $this->tableGateway->select( ['customer_id' => $customerId] );
	    
	    return $result;
    }
}