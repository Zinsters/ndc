<?php

/**
 * Model_Table_Products
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Products extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'webshop_products';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Product';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('id' );
	
	/**
	 * Dependent tables
	 *
	 * @var array
	 */
	protected $_dependentTables = array ('Model_Table_Consults', 'Model_Table_Invoicelines' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Productcategory' => array ('columns' => array ('group_id' ), 'refTableClass' => 'Model_Table_Productcategories', 'refColumns' => array ('id' ) ), 'Vat' => array ('columns' => array ('vat_id' ), 'refTableClass' => 'Model_Table_Vat', 'refColumns' => array ('id' ) ) );
	
	/**
	 * Return list of consults
	 *
	 * @return Zend_Db_Table_Rowset
	 */
	public function getConsults() {
		$select = $this->select ();
		$select->where ( 'is_consult=1' );
		$select->where ( 'active=1' );
		$select->order ( 'consult_duration' );
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return list of customers by defined request
	 *
	 * @param stdClass $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByRequest($params) {
		if (! $params->order)
			$params->order = 'productcode';
		if (! $params->direction)
			$params->direction = 'ASC';
		
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('p' => 'webshop_products' ) );
		$select->joinLeft ( array ('c' => 'webshop_groups' ), 'c.id=p.group_id', array ('category' => 'c.name' ) );
		$select->joinLeft ( array ('s' => 'webshop_suppliers' ), 's.id=p.supplier_id', array ('supplier' => 's.name' ) );
		
		$select->where ( 'p.active=1' );
		if ( isset( $params->productcode ) ) {
			$select->where ( "productcode like '" . $params->productcode . "%'" );
		}
		if ( isset( $params->name ) ) {
			$select->where ( "p.name like '" . $params->name . "%'" );
		}
		if ( isset( $params->category ) ) {
			$select->where ( "c.name like '" . $params->category . "%'" );
		}
		if ( isset( $params->size ) ) {
			$select->where ( "size like '" . $params->size . "%'" );
		}
		if ( isset( $params->price ) ) {
			$select->where ( "price_consumer_incl_vat like '" . $params->price . "%'" );
		}
		if ( isset( $params->supplier ) ) {
			$select->where ( "s.name like '" . $params->supplier . "%'" );
		}
		if (isset ( $params->is_consult ) ) {
			if ($params->is_consult == 1)
				$select->where ( "p.is_consult=1" );
			else
				$select->where ( "p.is_consult<>1" );
		}
		
		if ($params->order == 'name')
			$select->order ( 'p.name ' . $params->direction );
		else
			$select->order ( $params->order . ' ' . $params->direction );
		
		if ( isset( $params->page ) && isset( $params->rows ) )
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		$select->distinct ();
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return products ordered by suppliers to form orders
	 *
	 * @param int $locationId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getProductsToOrders($locationId) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('p' => 'webshop_products' ) );
		$select->join ( array ('il' => 'ndc_invoice_lines' ), 'p.id=il.product_id', array ('number' => 'SUM(number)' ) );
		$select->join ( array ('i' => 'ndc_invoices' ), 'i.id=il.invoice_id', array () );
		
		$select->group ( 'p.id' );
		
		$select->where ( 'p.active=1' );
		$select->where ( 'il.order_status="created"' );
		$select->where ( 'i.status<>"open"' );
		$select->where ( "i.location_id=$locationId" );
		$select->where ( 'p.is_consult<>1' );
		
		$select->order ( 'p.supplier_id' );
		$select->order ( 'p.productcode' );
		
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return Top Products list 
	 *
	 * @param array $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getProductsTop($params) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('p' => 'webshop_products' ) );
		$select->join ( array ('il' => 'ndc_invoice_lines' ), 'p.id=il.product_id', array ('number_total' => 'SUM(number)', 'total_price_total' => 'SUM(total_price)' ) );
		$select->join ( array ('i' => 'ndc_invoices' ), 'i.id=il.invoice_id', array () );
		
		$select->group ( 'p.id' );
		
		$select->where ( 'p.active=1' );
		$select->where ( 'i.status<>"open"' );
		if ( isset( $params->employeeId ) )
			$select->where ( 'i.employee_id=' . $params->employeeId );
		if ( isset( $params->locationId ) )
			$select->where ( 'i.location_id=' . $params->locationId );
		if ( isset ( $params->date_start ) )
			$select->where ( 'TO_DAYS(i.created)>=TO_DAYS("' . $params->date_start . '")' );
		if ( isset ( $params->date_end ) )
			$select->where ( 'TO_DAYS(i.created)<=TO_DAYS("' . $params->date_end . '")' );
		
		$select->order ( 'total_price_total DESC' );
		
		if ( isset( $params->page ) && isset( $params->rows ) )
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		$select->distinct ();
		return $this->fetchAll ( $select );
	}

}
