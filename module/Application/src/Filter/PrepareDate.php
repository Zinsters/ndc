<?php
namespace Application\Filter;

use Zend\Filter\FilterInterface;

class PrepareDate implements FilterInterface {
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