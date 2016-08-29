<?php

/**
 * Model_Table_Notes
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Notes extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_notes';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Note';
	
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
	protected $_referenceMap = array ('Customer' => array ('columns' => array ('customer_id' ), 'refTableClass' => 'Model_Table_Customers', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Employee' => array ('columns' => array ('employee_id' ), 'refTableClass' => 'Model_Table_Employees', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return list of notes for defined customer
	 *
	 * @param int $id
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByCustomerId($id) {
		$select = $this->select ();
		$select->where ( 'customer_id=' . $id );
		$select->order ( 'created DESC' );
		return $this->fetchAll ( $select );
	}

}