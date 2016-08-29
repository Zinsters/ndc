<?php

/**
 * Model_Table_Row_Product
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_Product extends Model_Table_Row_Abstract {
	
	/**
	 * Return product category
	 *
	 * @return Model_Table_Row_Productcategory
	 */
	public function getProductCategory() {
		return $this->findParentRow ( 'Model_Table_Productcategories' );
	}
	
	/**
	 * Return vat%
	 *
	 * @return float
	 */
	public function getVatPercent() {
		$vat = $this->findParentRow ( 'Model_Table_Vat' );
		if ($vat instanceof Model_Table_Row_Vat)
			return $vat->rate;
		else
			return 0;
	}
	
	public function getProducts($hideForCustomersOnly = 0) {
		$products = new Model_Table_Products ( );
		
		$mainProductId = ($this->main_product_id ? $this->main_product_id : $this->id);
		$select = $products->select ();
		$select->where ( 'id=' . $mainProductId . ' or main_product_id=' . $mainProductId );
		$select->where ( 'active=1' );
		$select->where ( 'webshop=1' );
		$select->where ( 'image1_ext<>""' );
		if ($hideForCustomersOnly)
			$select->where ( 'for_customers_only<>1' );
		$select->order = 'name';
		
		return $products->fetchAll ( $select );
	}
}
