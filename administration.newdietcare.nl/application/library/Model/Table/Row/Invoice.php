<?php

/**
 * Model_Table_Row_Invoice
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Invoice extends Model_Table_Row_Abstract {
	
	/**
	 * Return customer
	 *
	 * @return Model_Table_Row_Customer
	 */
	public function getCustomer() {
		return $this->findParentRow ( 'Model_Table_Users', 'Customer' );
	}
	
	/**
	 * Return employee created this invoice
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
	 * Return invoice lines, sorted by position 
	 *
	 */
	public function getLines() {
		if ($this->id) {
			$invoicelines = new Model_Table_Invoicelines ( );
			$select = $invoicelines->select ();
			$select->order ( 'position' );
			return $this->findDependentRowset ( 'Model_Table_Invoicelines', 'Invoice', $select );
		} else
			return null;
	}

}
