<?php

/**
 * Model_Table_Sessions
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Sessions extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'admin_sessions';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Session';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('hash' );
	
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
