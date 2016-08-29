<?php

/**
 * Print date
 * Format can be set in object constructor 
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrintDate implements Zend_Filter_Interface {
	
	/**
	 * Precision of returned value
	 *
	 * @var int
	 */
	private $format;
	
	/**
	 * Constructor
	 *
	 * @param int $precision
	 */
	public function __construct($format = 'dd-MM-yyyy') {
		$this->format = $format;
	}
	
	/**
	 * Filtration
	 *
	 * @param  string $value
	 * @return string
	 */
	public function filter($value) {
		$date = new Zend_Date ( );
		
		$withoutTime = explode ( ' ', $value );
		$dateParts = explode ( '-', $withoutTime [0] );
		
		if (( int ) $dateParts [0] && ( int ) $dateParts [1] && ( int ) $dateParts [2]) {
			$date->set ( $dateParts [2], Zend_Date::DAY );
			$date->set ( $dateParts [1], Zend_Date::MONTH );
			$date->set ( $dateParts [0], Zend_Date::YEAR );
			
			return $date->get ( $this->format, 'nl_NL' );
		} else
			return '';
	}

}
