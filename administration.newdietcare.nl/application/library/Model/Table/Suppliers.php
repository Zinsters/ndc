<?php

/**
 * Model_Table_Suppliers
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Suppliers extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'webshop_suppliers';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Supplier';
	
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
	protected $_dependentTables = array ('Model_Table_Products', 'Model_Table_Orders' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ();
	
	/**
	 * Return list of suppliers by defined request
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
		if ($params->name) {
			$select->where ( "name like '" . $params->name . "%'" );
		}
		if ($params->email) {
			$select->where ( "email like '" . $params->email . "%'" );
		}
		
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ($params->page && $params->rows)
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		return $this->fetchAll ( $select );
	}

}
