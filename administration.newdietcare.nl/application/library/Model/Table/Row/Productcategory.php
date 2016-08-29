<?php

/**
 * Model_Table_Row_Productcategory
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Productcategory extends Model_Table_Row_Abstract {
	
	public function getProducts($hideForCustomersOnly = 0) {
		$products = new Model_Table_Products ( );
		
		$select = $products->select ();
		$select->where ( 'main_product_id=0' );
		$select->where ( 'active=1' );
		$select->where ( 'webshop=1' );
		if ($hideForCustomersOnly)
			$select->where ( 'for_customers_only<>1' );
		$select->order = 'name';
		
		return $this->findDependentRowset ( 'Model_Table_Products', 'Productcategory', $select );
	}

}
