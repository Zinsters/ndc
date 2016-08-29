<?php

/**
 * Model_Table_Row_Orderline
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Orderline extends Model_Table_Row_Abstract {
	
	/**
	 * Return product included in this line
	 *
	 * @return Model_Table_Row_Product
	 */
	public function getProduct() {
		return $this->findParentRow ( 'Model_Table_Products' );
	}

}
