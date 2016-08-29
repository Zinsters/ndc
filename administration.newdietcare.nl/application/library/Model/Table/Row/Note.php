<?php

/**
 * Model_Table_Row_Note
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Note extends Model_Table_Row_Abstract {
	
	/**
	 * Return employee created this note
	 *
	 * @return Model_Table_Row_Employee
	 */
	public function getEmployee() {
		return $this->findParentRow ( 'Model_Table_Employees' );
	}
	
	/**
	 * Return location
	 *
	 * @return Model_Table_Row_Location
	 */
	public function getLocation() {
		return $this->findParentRow ( 'Model_Table_Locations' );
	}

}
