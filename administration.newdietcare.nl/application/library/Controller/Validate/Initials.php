<?php

/**
 * Class implements initials validation (dot is allowed)
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Validate_Initials extends Zend_Validate_Abstract {
	
	const NOT_INITIALS = 'valueNotUnique';
	
	/**
	 * @var array
	 */
	protected $_messageTemplates = array (self::NOT_INITIALS => "'%value%' has wrong characters" );
	
	/**
	 * Validation
	 * 
	 * @param void $email
	 * @return boolean
	 */
	public function isValid($value) {
		$valueString = ( string ) $value;
		$this->_setValue ( $valueString );
		
		$pattern = '/[^[:alpha:]\.]/';
		$result = preg_replace ( $pattern, '', $valueString );
		if ($valueString != $result) {
			$this->_error ();
			return false;
		}
		
		return true;
	}
}