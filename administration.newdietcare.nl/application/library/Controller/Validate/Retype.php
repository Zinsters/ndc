<?php

/**
 * Class implements write password retype validation
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Validate_Retype extends Zend_Validate_Abstract {
	
	const RETYPE_WRONG = 'retypeWrong';
	
	/**
	 * @var array
	 */
	protected $_messageTemplates = array (self::RETYPE_WRONG => "The passwords do not match" );
	
	/**
	 * Retyped password
	 *
	 * @var string
	 */
	private $passw2;
	
	/**
	 * Constructor
	 *
	 * @param string $passw2
	 */
	public function __construct($passw2) {
		$this->passw2 = $passw2;
	}
	
	/**
	 * Validation
	 * 
	 * @param void $passw1
	 * @return boolean
	 */
	public function isValid($passw1) {
		$valueString = ( string ) $passw1;
		$this->_setValue ( $valueString );
		
		if ($passw1 != $this->passw2) {
			$this->_error ();
			return false;
		}
		
		return true;
	}
}