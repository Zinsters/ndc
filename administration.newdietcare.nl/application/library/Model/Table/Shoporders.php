<?php

/**
 * Model_Table_Shoporders
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Shoporders extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'webshop_orders';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Shoporder';
	
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
	protected $_referenceMap = array ('Customer' => array ('columns' => array ('userid' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
}
