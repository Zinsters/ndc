<?php

/**
 * Model_Table_Employees
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Employees extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_employees';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Employee';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('id' );
	
	/**
	 * Dependent tables
	 *
	 * @var array
	 */
	protected $_dependentTables = array ('Model_Table_Consults', 'Model_Table_Invoices', 'Model_Table_Appointments', 'Model_Table_Employeeslocations' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ();
	
	/**
	 * Return employees by location id
	 * 
	 * @param $id
	 * @return Model_Table_Row_Abstract
	 */
	public function getByLocationId($id, $employeeId = null) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('e' => 'ndc_employees' ), array ('id' => 'e.id', 'name' ) );
		$select->join ( array ('el' => 'ndc_employees_locations' ), 'e.id=el.employee_id', array () );
		$select->join ( array ('l' => 'users_data' ), 'l.userid=el.location_id', array () );
		
		$select->where ( 'e.active=1' );
		$select->where ( 'l.userid=' . $id );
		if ($employeeId)
			$select->where ( 'e.id=' . $employeeId );
		
		$select->order ( 'e.name' );
		$select->distinct ();
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return list of employees by defined request
	 *
	 * @param stdClass $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByRequest($params) {
		if (! $params->order)
			$params->order = 'name';
		if (! $params->direction)
			$params->direction = 'ASC';
		
		$select = $this->select ();
		
		$select->where ( 'active=1' );
		if ( isset( $params->name ) ) {
			$select->where ( "name like '" . $params->name . "%'" );
		}
		
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ( isset( $params->page ) && isset( $params->rows ) )
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		return $this->fetchAll ( $select );
	}

}
