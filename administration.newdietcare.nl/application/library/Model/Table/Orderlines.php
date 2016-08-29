<?php

/**
 * Model_Table_Orderlines
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Orderlines extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_order_lines';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Orderline';
	
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
	protected $_referenceMap = array ('Order' => array ('columns' => array ('order_id' ), 'refTableClass' => 'Model_Table_Orders', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Product' => array ('columns' => array ('product_id' ), 'refTableClass' => 'Model_Table_Products', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return set of order lines
	 *
	 * @param int $id
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByOrderId($id) {
		return $this->fetchAll ( "order_id=$id", 'id' );
	}

}