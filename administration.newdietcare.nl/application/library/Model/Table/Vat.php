<?php

/**
 * Model_Table_Vat
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Vat extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'webshop_vat';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Vat';
	
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
	 * Return VAT low rate
	 *
	 * @return float
	 */
	public function getVatLow() {
		$result = $this->fetchRow ( 'name="BTW laag"' );
		if ($result)
			return $result->rate;
		else
			return 0;
	}
	
	/**
	 * Return VAT high rate
	 *
	 * @return float
	 */
	public function getVatHigh() {
		$result = $this->fetchRow ( 'name="BTW hoog"' );
		if ($result)
			return $result->rate;
		else
			return 0;
	}
	
	/**
	 * Return list of vat rates by defined request
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
		
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ($params->page && $params->rows)
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		return $this->fetchAll ( $select );
	}

}
