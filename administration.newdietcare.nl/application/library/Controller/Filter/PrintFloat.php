<?php

/**
 * Print float value in 1000,00 format
 * Precision can be set in object constructor, 1 is default
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrintFloat implements Zend_Filter_Interface {

	/**
	 * Precision of returned value
	 *
	 * @var int
	 */
	private $precision;

	/**
	 * Constructor
	 *
	 * @param int $precision
	 */
	public function __construct($precision = 1) {
		$this->precision = $precision;
	}

	/**
	 * Filtration
	 *
	 * @param  float $value
	 * @return string
	 */
	public function filter($value) {
		if ($value) {
			return number_format ( $value, $this->precision, ',', '' );
		} else {
			return '';
		}
	}

}
