<?php

/**
 * Prepare date to the necessary format (%d-%m-%Y)
 * '%d-%m-%Y' and '%d/%m/%Y' are acceptable
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Controller_Filter_PrepareDate implements Zend_Filter_Interface {
	
	/**
	 * Filtration
	 *
	 * @param  string $value
	 * @return string
	 */
	public function filter($value) {
		$step1 = str_replace ( '/', '-', $value );
		$step2 = explode ( '-', $step1 );
		if (isset ( $step2 [0] ) && isset ( $step2 [1] ) && isset ( $step2 [2] ))
			$step3 = $step2 [2] . '-' . $step2 [1] . '-' . $step2 [0];
		else
			$step3 = $value;
		return $step3;
	}

}
