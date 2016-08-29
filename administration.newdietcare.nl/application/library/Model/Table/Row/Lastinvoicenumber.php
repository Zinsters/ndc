<?php

/**
 * Model_Table_Row_Lastinvoicenumber
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Lastinvoicenumber extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * Return invoicenumber for new invoices
	 *
	 * @return string
	 */
	public function getNumber() {
		return substr ( $this->year, 2 ) . '-' . $this->number;
	}

}
