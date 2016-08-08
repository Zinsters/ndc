<?php
namespace Application\Filter;

use Zend\Filter\FilterInterface;
use DateTime;

class PrintDate implements FilterInterface {
	private $format;

	public function __construct($format = 'd-m-Y') {
		$this->format = $format;
	}
	
	public function filter($value) {
		$date = new DateTime ( );
		
		$withoutTime = explode ( ' ', $value );
		$dateParts = explode ( '-', $withoutTime [0] );
		
		if (( int ) $dateParts [0] && ( int ) $dateParts [1] && ( int ) $dateParts [2]) {
			$date->setDate( $dateParts [0], $dateParts [1], $dateParts [2] );
			
			return $date->format ( $this->format );
		} else
			return '';
	}
}