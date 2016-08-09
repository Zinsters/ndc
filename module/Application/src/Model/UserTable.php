<?php
namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class UserTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getByRequest($params)
    {
        $result = $this->tableGateway->select(function(Select $select) use ($params) {
			if ( empty( $params->order ) )
				$params->order = 'achternaam';
			if ( empty( $params->direction ) )
				$params->direction = 'ASC';
		
			$select->where ( '`group` = 19' );
		
			if (isset($params->achternaam)) {
				$select->where->like ( 'achternaam', $params->achternaam . '%' );
			}
			if (isset($params->voornaam)) {
				$select->where->like ( 'voornaam', $params->voornaam . '%' );
			}
			if (isset($params->geboortedatum)) {
				$select->where->like ( 'geboortedatum', $params->geboortedatum . '%' );
			}
			if (isset($params->thuisadres)) {
				$select->where ( 'thuisadres', $params->thuisadres . '%' );
			}
			if (isset($params->thuisplaats)) {
				$select->where ( 'thuisplaats', $params->thuisplaats . '%' );
			}
			if (isset($params->thuispostcode)) {
				$select->where ( 'thuispostcode', $params->thuispostcode . '%' );
			}
			if (isset($params->email)) {
				$select->where ( 'email' . $params->email . '%' );
			}
		
			$select->order ( $params->order . ' ' . $params->direction );
		
			if (isset($params->page) && isset($params->rows))
				$select->limit ( $params->rows )->offset ( $params->rows * $params->page - $params->rows );
	    });
	    
	    return $result;
    }
    
    public function getForExport($params)
    {
        $result = $this->tableGateway->select(function(Select $select) use ($params) {
			$select->where ( '`group` = 19' );
			$select->where ( 'active = 1' );
		
			$select->order ( 'userid' );
			$select->group ( 'userid' );
	    });
	    
	    return $result;
    }
}