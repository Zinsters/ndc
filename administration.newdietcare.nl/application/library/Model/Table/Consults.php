<?php

/**
 * Model_Table_Consults
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Consults extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_consults';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Consult';
	
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
	protected $_referenceMap = array ('Customer' => array ('columns' => array ('customer_id' ), 'refTableClass' => 'Model_Table_Customers', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Product' => array ('columns' => array ('product_id' ), 'refTableClass' => 'Model_Table_Products', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Employee' => array ('columns' => array ('employee_id' ), 'refTableClass' => 'Model_Table_Employees', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return list of consults for defined customer
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
