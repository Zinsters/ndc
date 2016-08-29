<?php

/**
 * Model_Table_Ordres
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Orders extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_orders';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Order';
	
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
	protected $_dependentTables = array ('Model_Table_Orderlines' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Supplier' => array ('columns' => array ('supplier_id' ), 'refTableClass' => 'Model_Table_Suppliers', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return invoices wich are not sent yet
	 *
	 * @param int $locationId
	 * @param int $supplierId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getCreated($locationId, $supplierId = null) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('o' => 'ndc_orders' ) );
		$select->joinLeft ( array ('s' => 'webshop_suppliers' ), 'o.supplier_id=s.id', array ('supplier' => 's.name' ) );
		$select->where ( "location_id=$locationId" );
		if ($supplierId !== null)
			$select->where ( "supplier_id='$supplierId'" );
		$select->where ( 'status="created"' );
		$select->order ( 's.name' );
		
		$select->distinct ();
		return $this->fetchAll ( $select );
	}

}
