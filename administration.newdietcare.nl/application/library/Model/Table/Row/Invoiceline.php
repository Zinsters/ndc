<?php

/**
 * Model_Table_Row_Invoiceline
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Invoiceline extends Model_Table_Row_Abstract {
	
	/**
	 * Return product
	 *
	 * @return Model_Table_Row_Product
	 */
	public function getProduct() {
		return $this->findParentRow ( 'Model_Table_Products' );
	}

}
