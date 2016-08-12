<?php
namespace Application\Model\Db\Row;

use Application\Filter\PrintDate;

abstract class AbstractRow
{
    public function exchangeArray(array $data)
    {
		foreach( $data as $key => $value ) {
			$this->$key = $value;
		}
    }

	public function dateFormat($fieldName, $formatString = '%e %b %Y') {
		$printDate = new PrintDate( $formatString );
		return $printDate->filter( $this->$fieldName );
	}
}