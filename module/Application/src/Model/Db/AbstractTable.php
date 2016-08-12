<?php
namespace Application\Model\Db;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

abstract class AbstractTable
{
    protected $_tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->_tableGateway = $tableGateway;
    }

    public function getById($id)
    {
        $id = (int) $id;
        $rowset = $this->_tableGateway->select(function(Select $select) use ($id) {
        	$select->where ( sprintf( 'id = %d', $id ) );
        });
        $row = $rowset->current();
        if (! $row) {
			return null;
        }

        return $row;
    }
}