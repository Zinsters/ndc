<?php

/**
 * Model_Table_Abstract
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

abstract class Model_Table_Abstract extends Zend_Db_Table_Abstract {
	
	/**
	 * Return row by primary key
	 * 
	 * @param int $id
	 * @return Model_Table_Row_Abstract
	 */
	public function getById($id) {
		$row = $this->find ( $id );
		if ($row)
			return $row->current ();
		else
			return null;
	}

}

