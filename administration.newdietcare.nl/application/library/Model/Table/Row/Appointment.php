<?php

/**
 * Model_Table_Row_Appointment
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Appointment extends Model_Table_Row_Abstract {
	
	/**
	 * Return customer
	 *
	 * @return Model_Table_Row_Customer
	 */
	public function getCustomer() {
		return $this->findParentRow ( 'Model_Table_Users', 'Customer' );
	}
	
	/**
	 * Return employee
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

}
