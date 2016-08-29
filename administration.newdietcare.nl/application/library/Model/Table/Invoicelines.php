<?php

/**
 * Model_Table_Invoicelines
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Invoicelines extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_invoice_lines';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Invoiceline';
	
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
	protected $_referenceMap = array ('Invoice' => array ('columns' => array ('invoice_id' ), 'refTableClass' => 'Model_Table_Invoices', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Product' => array ('columns' => array ('product_id' ), 'refTableClass' => 'Model_Table_Products', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );

}