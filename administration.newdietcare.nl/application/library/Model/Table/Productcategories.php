<?php

/**
 * Model_Table_Productcategories
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Productcategories extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'webshop_groups';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Productcategory';
	
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
	protected $_dependentTables = array ('Model_Table_Products' );
	
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
		
		if ($params->name) {
			$select->where ( "name like '" . $params->name . "%'" );
		}
		
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ($params->page && $params->rows)
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		return $this->fetchAll ( $select );
	}

}
