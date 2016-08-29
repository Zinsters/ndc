<?php

/**
 * Model_Table_Lastinvoicenumber
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Lastinvoicenumber extends Zend_Db_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_lastinvoicenumber';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Lastinvoicenumber';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('year' );
	
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
	protected $_referenceMap = array ();

}