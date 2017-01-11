<?php

/**
 * Prepare float value to the necessary format (1000.00)
 * '1000.00' and '1000,00' are acceptable
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrepareFloat implements Zend_Filter_Interface {
	
	/**
	 * Filtration
	 *
	 * @param  string $value
	 * @return string
	 */
	public function filter($value) {
        return $value ? str_replace ( ',', '.', $value ) : 0;
	}

}
