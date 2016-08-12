<?php
namespace Application\Model\Db;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class ConsultTable extends AbstractTable
{
    public function getByCustomerId($customerId)
    {
        $customerId = (int) $customerId;
        $result = $this->_tableGateway->select(function(Select $select) use ($customerId) {
        	$select->where ( sprintf( 'customer_id = %d', $customerId ) );        	
        	$select->order ( 'created DESC' );
        });

        return $result;
    }
    
    public function getLastConsult($customerId)
    {
        $customerId = (int) $customerId;
        $rowset = $this->_tableGateway->select(function(Select $select) use ($customerId) {
        	$select->where ( sprintf( 'customer_id = %d', $customerId ) );        	
        	$select->order ( 'created DESC' );
        });
        $row = $rowset->current();
        if (! $row) {
			return null;
        }

        return $row;
    }
}