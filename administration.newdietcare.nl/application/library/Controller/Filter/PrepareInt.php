<?php

/**
 * Prepare int value (0 by default)
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrepareInt implements Zend_Filter_Interface {
	
	/**
	 * Filtration
	 *
	 * @param  string $value
	 * @return string
	 */
	public function filter($value) {
        return $value ? $value : 0;
	}

}
