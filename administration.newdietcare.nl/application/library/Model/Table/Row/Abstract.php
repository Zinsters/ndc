<?php

/**
 * Model_Table_Row_Abstract
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

abstract class Model_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * Executes date formatting form MYSQL data
	 *
	 * @param string $fieldName
	 * @param string $formatString
	 * @return string
	 */
	public function dateFormat($fieldName, $formatString = '%e %b %Y') {
		$db = Zend_Registry::get ( 'db' );
		$result = $db->fetchAll ( "SELECT date_format('" . $this->$fieldName . "', '$formatString') as d" );
		
		if (is_array ( $result ))
			return $result [0] ['d'];
		else
			return '';
	}
}
