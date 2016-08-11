<?php
namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

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
				$select->where->like ( 'thuisadres', $params->thuisadres . '%' );
			}
			if (isset($params->thuisplaats)) {
				$select->where->like ( 'thuisplaats', $params->thuisplaats . '%' );
			}
			if (isset($params->thuispostcode)) {
				$select->where->like ( 'thuispostcode', $params->thuispostcode . '%' );
			}
			if (isset($params->email)) {
				$select->where->like ( 'email', $params->email . '%' );
			}
		
			$select->order ( $params->order . ' ' . $params->direction );
		
			if (isset($params->page) && isset($params->rows))
				$select->limit ( $params->rows )->offset ( $params->rows * $params->page - $params->rows );
	    });
	    
	    return $result;
    }
    
    public function getForExport($customerExport)
    {
        $result = $this->tableGateway->select(function(Select $select) use ($customerExport) {
			$select->join ( 'ndc_mijn_ndc_points', 'userid = ndc_mijn_ndc_points.user_id', array ('last_measurement' => new Expression( 'max(ndc_mijn_ndc_points.date)' ) ), $select::JOIN_LEFT );

			$select->where ( '`group` = 19' );
			$select->where ( 'active = 1' );

			if ( ! empty( $customerExport->reg_date_year ) )
				$select->where( sprintf( 'DATE_FORMAT(FROM_UNIXTIME(reg_date), \'%%Y\') = %s', $customerExport->reg_date_year ) );
			if ( ! empty( $customerExport->reg_date_from ) )
				$select->where( sprintf( 'TO_DAYS(FROM_UNIXTIME(reg_date)) >= TO_DAYS(\'%s\')', $customerExport->reg_date_from ) );
			if ( ! empty( $customerExport->reg_date_to ) )
				$select->where( sprintf( 'TO_DAYS(FROM_UNIXTIME(reg_date)) <= TO_DAYS(\'%s\')', $customerExport->reg_date_to ) );
			if ( ! empty( $customerExport->no_measurements ) ) {
				$date = date ( 'Y-m-d' );
				$dateParts = explode ( '-', $date );
				$monthSubtract = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1] - $customerExport->no_measurements, $dateParts [2], $dateParts [0] ) );
				$select->where( sprintf( 'TO_DAYS(FROM_UNIXTIME(reg_date)) < TO_DAYS(\'%s\')', $monthSubtract ) );
				$select->having( sprintf( 'TO_DAYS(last_measurement) < TO_DAYS(\'%s\')', $monthSubtract ) );
			}
			if ( ! empty( $customerExport->with_birthday ) ) {
				$select->where( 'TO_DAYS( geboortedatum ) > 0' );
			}		

			$select->order ( 'userid' );
			$select->group ( 'userid' );
	    });
	    
	    return $result;
    }
}