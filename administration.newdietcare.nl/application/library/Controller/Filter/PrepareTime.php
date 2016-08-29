<?php

/**
 * Prepare time to the necessary format
 * :;,. dividers are acceptable
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrepareTime implements Zend_Filter_Interface {
	
	/**
	 * Filtration
	 *
	 * @param  string $value
	 * @return string
	 */
	public function filter($value) {
		return str_replace ( array (';', ',', '.' ), ':', $value );
	}

}
