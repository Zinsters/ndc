<?php

/**
 * Model_Table_Employeeslocations
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Employeeslocations extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_employees_locations';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Employeelocation';
	
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
	protected $_dependentTables = array ();
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Employee' => array ('columns' => array ('employee_id' ), 'refTableClass' => 'Model_Table_Employees', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Locations', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );

}