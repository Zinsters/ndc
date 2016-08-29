<?php

/**
 * Model_Table_Row_Consult
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Consult extends Model_Table_Row_Abstract {
	
	/**
	 * Return employee created this consult
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
		return $this->findParentRow ( 'Model_Table_Users', 'Location' );
	}
	
	/**
	 * Return product
	 *
	 * @return Model_Table_Row_Product
	 */
	public function getProduct() {
		return $this->findParentRow ( 'Model_Table_Products' );
	}

}
