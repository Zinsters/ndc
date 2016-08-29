<?php

/**
 * Model_Table_Appointments
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Appointments extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_appointments';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Appointment';
	
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
	protected $_dependentTables = array ( );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Customer' => array ('columns' => array ('customer_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Employee' => array ('columns' => array ('employee_id' ), 'refTableClass' => 'Model_Table_Employees', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return list of of appointments for defined date and employee
	 *
	 * @param int $employeeId
	 * @param string $date
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByEmployeeAndDate($employeeId, $date) {
		$select = $this->select ();
		$select->from ( array ('a' => 'ndc_appointments' ), array ('id' => 'id', 'customer_id' => 'customer_id', 'location_id' => 'location_id', 'date' => 'date', 'time_start' => 'time_start', 'time_end' => 'time_end', 'time_difference' => 'round((time_to_sec(time_end)-time_to_sec(time_start))/60)', 'style' => 'style' ) );
		$select->where ( "employee_id=$employeeId" );
		$select->where ( "date='$date'" );
		$select->order ( 'time_start' );
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Check is selected period is free
	 *
	 * @param int $employeeId
	 * @param int $date
	 * @param string $timeStart
	 * @param string $timeEnd
	 * @param int $id
	 * @return boolean
	 */
	public function periodIsFree($employeeId, $date, $timeStart, $timeEnd, $id = null) {
		$select = $this->select ();
		$select->where ( "employee_id=$employeeId" );
		$select->where ( "date='$date'" );
		$select->where ( "time_start>='$timeStart'" );
		$select->where ( "time_end<='$timeEnd'" );
		if ($id)
			$select->where ( "id<>$id" );
		$result = $this->fetchAll ( $select );
		return count ( $result ) == 0;
	}

}
