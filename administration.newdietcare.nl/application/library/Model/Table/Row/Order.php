<?php

/**
 * Model_Table_Row_Order
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Order extends Model_Table_Row_Abstract {
	
	/**
	 * Return the name of the order supplier
	 *
	 * @return string
	 */
	public function getSupplierName() {
		if ($this->supplier_id) {
			return $this->findParentRow ( 'Model_Table_Suppliers' )->name;
		} else
			return 'No supplier';
	}
}
