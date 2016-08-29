<?php

/**
 * Class implements uniqueness validation
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Validate_Uniq extends Zend_Validate_Abstract {
	
	const NOT_UNIQUE = 'valueNotUnique';
	
	/**
	 * @var array
	 */
	protected $_messageTemplates = array (self::NOT_UNIQUE => "Value '%value%' already exists" );
	
	/**
	 * Name of the table for validation
	 *
	 * @var string
	 */
	private $tableName;
	
	/**
	 * Name of the field for validation
	 *
	 * @var string
	 */
	private $fieldName;
	
	/**
	 * User id
	 * 
	 * @var int
	 */
	private $id;
	
	/**
	 * Constructor
	 *
	 * @param string $tableName
	 * @param string $fieldName
	 * @param void $id
	 */
	public function __construct($tableName, $fieldName, $id = null) {
		$this->tableName = $tableName;
		$this->fieldName = $fieldName;
		$this->id = $id;
	}
	
	/**
	 * Validation
	 * 
	 * @param void $email
	 * @return boolean
	 */
	public function isValid($value) {
		$valueString = ( string ) $value;
		$this->_setValue ( $valueString );
		
		$tableName = $this->tableName;
		$table = new $tableName ( );
		$where = array ($this->fieldName . ' = ?' => mysql_escape_string ( $value ) );
		$row = $table->fetchRow ( $where );
		if ($row && $row->userid != $this->id) {
			$this->_error ();
			return false;
		}
		
		return true;
	}
}
