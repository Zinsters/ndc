<?php
use Dompdf\Dompdf;

/**
 * Class Default_AdminController
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Default_AdminController extends Controller_Default {
	
	///////////////////
	// Overview part //
	///////////////////
	

	/**
	 * Return current role of the user
	 *
	 */
	public function getRole() {
		return 'admin';
	}
	
	public function preDispatch() {
		parent::preDispatch ();
		
		if (! isset ( $this->view->admin ))
			$this->_redirect ( '/' );
	}
	
	/**
	 * The default controller action (sales)
	 *
	 */
	public function indexAction() {
		if ($this->_request->getParam ( 'filter_employee' ) || $this->_request->getParam ( 'filter_location' ) || $this->_request->getParam ( 'filter_paymentmethod' ) || $this->_request->getParam ( 'filter_date_start' ) || $this->_request->getParam ( 'filter_date_end' ) || $this->_request->getParam ( 'filter_price_start' ) || $this->_request->getParam ( 'filter_price_end' )) {
			$userData = $this->_request->getParams ();
			$filters = array ('filter_employee' => array ('Int' ), 'filter_location' => array ('Int' ), 'filter_paymentmethod' => array ('StringTrim', 'StripTags' ), 'filter_date_start' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ), 'filter_date_end' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ), 'filter_price_start' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ), 'filter_price_end' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ) );
            $validators = array ('filter_employee' => array ('Int', 'allowEmpty' => true ), 'filter_location' => array ('Int', 'allowEmpty' => true ), 'filter_paymentmethod' => array (new Zend_Validate_InArray ( array ('kas', 'pin', 'bank' ) ), 'allowEmpty' => true ), 'filter_date_start' => array ('Date', 'allowEmpty' => true ), 'filter_date_end' => array ('Date', 'allowEmpty' => true ), 'filter_price_start' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ), 'filter_price_end' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
		} else {
			$input = new stdClass ( );
			$input->filter_date_start = date ( 'Y-m-d' );
			$input->filter_date_end = $input->filter_date_start;
		}
		
		$db = Zend_Registry::getInstance ()->get ( 'db' );
		$db->setFetchMode ( Zend_Db::FETCH_OBJ );
		
		// Table with productcategories
		$query = '
			SELECT
				pc.name AS name,
				productcategory_id,
				SUM(total) AS total,
				SUM(total*percent) AS reduction,
				SUM((total-total*percent)*vat_high_percent/(100+vat_high_percent)) AS vat_high,
				SUM((total-total*percent)*vat_low_percent/(100+vat_low_percent)) AS vat_low				
				FROM
					(SELECT
						p.group_id AS productcategory_id,
						il.total_price AS total,
						i.reduction/i.total AS percent,
						IF ((v.name="BTW hoog"),il.vat_percent,0) AS vat_high_percent,
						IF ((v.name="BTW laag"),il.vat_percent,0) AS vat_low_percent						
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i, webshop_vat AS v
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.vat_id=v.id
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp
				LEFT JOIN webshop_groups as pc ON pp.productcategory_id=pc.id
				GROUP BY productcategory_id
				ORDER BY name';
		$this->view->lines = $db->fetchAll ( $query );
		
		// Products
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.is_consult<>1
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->products = $result [0]->total;
		
		// Consults
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.is_consult=1
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->consults = $result [0]->total;
		
		// Cash
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="kas"
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->cash = $result [0]->total;
		
		// Pin
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="pin"
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->pin = $result [0]->total;
		
		// Invoice
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="bank"
						AND i.status<>"open"';
		if ( ! empty( $input->filter_employee ) )
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ( ! empty( $input->filter_location ) )
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ( ! empty( $input->filter_paymentmethod ) )
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ( ! empty( $input->filter_date_start ) )
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ( ! empty( $input->filter_date_end ) )
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ( ! empty( $input->filter_price_start ) )
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ( ! empty( $input->filter_price_end ) )
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->invoice = $result [0]->total;
		
		$users = new Model_Table_Users ( );
		$this->view->filterLocations = $users->getLocations ();
		$employees = new Model_Table_Employees ( );
		$this->view->filterEmployees = $employees->fetchAll ( null, 'name' );
		
		$this->view->input = $input;
	}
	
	/**
	 * Sales overview in pdf format
	 *
	 */
	public function salespdfAction() {
		set_time_limit ( 180 );
		
		if ($this->_request->getParam ( 'filter_employee' ) || $this->_request->getParam ( 'filter_location' ) || $this->_request->getParam ( 'filter_paymentmethod' ) || $this->_request->getParam ( 'filter_date_start' ) || $this->_request->getParam ( 'filter_date_end' ) || $this->_request->getParam ( 'filter_price_start' ) || $this->_request->getParam ( 'filter_price_end' )) {
			$userData = $this->_request->getParams ();
			$filters = array ('filter_employee' => array ('Int' ), 'filter_location' => array ('Int' ), 'filter_paymentmethod' => array ('StringTrim', 'StripTags' ), 'filter_date_start' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ), 'filter_date_end' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ), 'filter_price_start' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ), 'filter_price_end' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ) );
            $validators = array ('filter_employee' => array ('Int', 'allowEmpty' => true ), 'filter_location' => array ('Int', 'allowEmpty' => true ), 'filter_paymentmethod' => array (new Zend_Validate_InArray ( array ('kas', 'pin', 'bank' ) ), 'allowEmpty' => true ), 'filter_date_start' => array ('Date', 'allowEmpty' => true ), 'filter_date_end' => array ('Date', 'allowEmpty' => true ), 'filter_price_start' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ), 'filter_price_end' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
		} else {
			$input = new stdClass ( );
			$input->filter_date_start = date ( 'Y-m-d' );
			$input->filter_date_end = $input->filter_date_start;
		}
		
		$db = Zend_Registry::getInstance ()->get ( 'db' );
		$db->setFetchMode ( Zend_Db::FETCH_OBJ );
		
		// Table with productcategories
		$query = '
			SELECT
				pc.name AS name,
				productcategory_id,
				SUM(total) AS total,
				SUM(total*percent) AS reduction,
				SUM((total-total*percent)*vat_high_percent/(100+vat_high_percent)) AS vat_high,
				SUM((total-total*percent)*vat_low_percent/(100+vat_low_percent)) AS vat_low				
				FROM
					(SELECT
						p.group_id AS productcategory_id,
						il.total_price AS total,
						i.reduction/i.total AS percent,
						IF ((v.name="BTW hoog"),il.vat_percent,0) AS vat_high_percent,
						IF ((v.name="BTW laag"),il.vat_percent,0) AS vat_low_percent						
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i, webshop_vat AS v
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.vat_id=v.id
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp
				LEFT JOIN webshop_groups as pc ON pp.productcategory_id=pc.id
				GROUP BY productcategory_id
				ORDER BY name';
		$this->view->lines = $db->fetchAll ( $query );
		
		// Products
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.is_consult<>1
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->products = $result [0]->total;
		
		// Consults
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND p.is_consult=1
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->consults = $result [0]->total;
		
		// Cash
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="kas"
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->cash = $result [0]->total;
		
		// Pin
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="pin"
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->pin = $result [0]->total;
		
		// Invoice
		$query = '
			SELECT
				SUM(total-total*percent) AS total
				FROM
					(SELECT
						il.total_price AS total,
						i.reduction/i.total AS percent
						FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
						WHERE p.id=il.product_id
						AND i.id=il.invoice_id
						AND i.paymentmethod="bank"
						AND i.status<>"open"';
		if ($input->filter_employee)
			$query .= 'AND i.employee_id=' . $input->filter_employee . ' ';
		if ($input->filter_location)
			$query .= 'AND i.location_id=' . $input->filter_location . ' ';
		if ($input->filter_paymentmethod)
			$query .= 'AND i.paymentmethod="' . $input->filter_paymentmethod . '" ';
		if ($input->filter_date_start)
			$query .= 'AND TO_DAYS(created)>=TO_DAYS("' . $input->filter_date_start . '") ';
		if ($input->filter_date_end)
			$query .= 'AND TO_DAYS(created)<=TO_DAYS("' . $input->filter_date_end . '") ';
		if ($input->filter_price_start)
			$query .= 'AND il.total_price/il.number>=' . $input->filter_price_start . ' ';
		if ($input->filter_price_end)
			$query .= 'AND il.total_price/il.number<=' . $input->filter_price_end . ' ';
		$query .= '
					)
				AS pp';
		$result = $db->fetchAll ( $query );
		$this->view->invoice = $result [0]->total;
		
		if ($input->filter_location) {
			$locations = new Model_Table_Locations ( );
			$this->view->filter_location = $locations->getById ( $input->filter_location )->getUsername ();
		}
		if ($input->filter_employee) {
			$employees = new Model_Table_Employees ( );
			$this->view->filter_employee = $employees->getById ( $input->filter_employee )->name;
		}
		
		$this->view->input = $input;
		
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
		
		$html = $this->view->render ( 'admin/salespdf.phtml' );
		
		$dompdf = new DOMPDF ( );
		$dompdf->set_paper ( 'a4' );
		$dompdf->load_html ( $html );
		
		$dompdf->render ();
		$outputString = $dompdf->output ();
		
		$this->getResponse ()->setHeader ( 'Content-Type', 'application/pdf' );
		$this->getResponse ()->setHeader ( 'Content-Length', strlen ( $outputString ) );
		$this->getResponse ()->setHeader ( 'Content-Disposition', "inline; filename=invoice.pdf" );
		
		echo $outputString;
	}
	
	/**
	 * Page with list of invoices (search, order)
	 *
	 */
	public function invoicesAction() {
		$users = new Model_Table_Users ( );
		$this->view->filterLocations = $users->getLocations ();
		$employees = new Model_Table_Employees ( );
		$this->view->filterEmployees = $employees->fetchAll ( null, 'name' );
	}
	
	/**
	 * Delete invoice
	 * Status must be 'open'
	 *
	 */
	public function invoicedelAction() {
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || $invoice->status != 'open') {
				$this->_redirect ( '/admin/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/invoices/' );
			return;
		}
		
		if ($this->_request->isPost ())
			$invoice->delete ();
		
		$this->_redirect ( '/admin/invoices/' );
	}
	
	/**
	 * Change invoice status to 'payed'
	 * - admin permission
	 * - payment_method must be 'bank'
	 * - status must be 'final'
	 *
	 */
	public function invoicepayedAction() {
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || $invoice->status != 'final' || $invoice->paymentmethod != 'bank') {
				$this->_redirect ( '/admin/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/invoices/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$invoice->status = 'payed';
			$invoice->save ();
		}
		
		$this->_redirect ( '/admin/invoices/' );
	}
	
	/**
	 * Return the invoice in the pdf format
	 *
	 */
	public function invoicepdfAction() {
		set_time_limit ( 180 );
		
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->status != 'final' && $invoice->status != 'payed')) {
				$this->_redirect ( '/admin/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/invoices/' );
			return;
		}
		
		$customer = $invoice->getCustomer ();
		
		$vat = new Model_Table_Vat ( );
		$vatLow = $vat->getVatLow ();
		$exVatLow = 0;
		$exVatHigh = 0;
		$invoicelines = $invoice->getLines ();
		foreach ( $invoicelines as $line ) {
			$koef1 = $line->vat_percent / (100 + $line->vat_percent);
			if ($vatLow == $line->vat_percent)
				$exVatLow += $line->total_price * $koef1;
			else
				$exVatHigh += $line->total_price * $koef1;
		}
		$koef2 = ($invoice->total - $invoice->reduction) / $invoice->total;
		$exVatExReductionLow = round ( $exVatLow * $koef2, 2 );
		$exVatExReductionHigh = round ( $exVatHigh * $koef2, 2 );
		
		$invoiceDetails = new Model_Table_Invoicedetails ( );
		$contentData = array ();
		$contentData ['invoiceDetails'] = $invoiceDetails->fetchRow ();
		$contentData ['invoice'] = $invoice;
		$contentData ['invoicelines'] = $invoicelines;
		$contentData ['exVatLow'] = $exVatExReductionLow;
		$contentData ['exVatHigh'] = $exVatExReductionHigh;
		$contentData ['baseUrl'] = $this->view->baseUrl;
		$contentData ['currentCustomer'] = $customer;
		$contentData ['printFloat'] = $this->view->printFloat;
		$contentData ['printPrice'] = $this->view->printPrice;
		$contentData ['nextAppointment'] = $this->view->nextAppointment;
		$contentData ['printFullDate'] = $this->view->printFullDate;
		$this->view->contentData = $contentData;
		
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
		
		$html = $this->view->render ( 'customer/invoicepdf.phtml' );
		
		$dompdf = new DOMPDF ( );
		$dompdf->set_paper ( 'a4' );
		$dompdf->load_html ( $html );
		
		$dompdf->render ();
		$outputString = $dompdf->output ();
		
		$this->getResponse ()->setHeader ( 'Content-Type', 'application/pdf' );
		$this->getResponse ()->setHeader ( 'Content-Length', strlen ( $outputString ) );
		$this->getResponse ()->setHeader ( 'Content-Disposition', "inline; filename=invoice.pdf" );
		
		echo $outputString;
	}
	
	/**
	 * Set invoice customer to current and make redirect to customer/invoice
	 *
	 */
	public function invoiceAction() {
		if ($this->_request->getParam ( 'id' )) {
			$defaultNamespace = new Zend_Session_Namespace ( 'default' );
			$invoices = new Model_Table_Invoices ( );
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if ($invoice instanceof Model_Table_Row_Invoice) {
				$customer = $invoice->getCustomer ();
				if ($customer instanceof Model_Table_Row_User) {
					$defaultNamespace->currentCustomerId = $customer->userid;
					$users = new Model_Table_Users ( );
					$location = $users->getById ( $invoice->location_id );
					$defaultNamespace->currentLocationId = $location->userid;
					$this->_redirect ( '/customer/invoice/id/' . $invoice->id . '/' );
					return;
				}
			}
		}
		
		$this->_redirect ( '/admin/invoices/' );
	}
	
	/**
	 * Page with Top Products (search)
	 *
	 */
	public function productstopAction() {
		$this->view->headcontent = 'admin/productstop/headcontent.phtml';
		
		$users = new Model_Table_Users ( );
		$this->view->filterLocations = $users->getLocations ();
		$employees = new Model_Table_Employees ( );
		$this->view->filterEmployees = $employees->fetchAll ( null, 'name' );
		
		$input = new stdClass ( );
		$input->filter_date_start = date ( 'Y-m-d' );
		$input->filter_date_end = $input->filter_date_start;
		$this->view->input = $input;
	}
	
	/**
	 * Year sales report
	 *
	 */
	public function yearAction() {
		set_time_limit ( 300 );
		
		$db = Zend_Registry::getInstance ()->get ( 'db' );
		$db->setFetchMode ( Zend_Db::FETCH_OBJ );
		
		$query = 'SELECT DATE_FORMAT(created,"%Y") AS year FROM ndc_invoices GROUP BY DATE_FORMAT(created,"%Y")';
		$this->view->years = $db->fetchAll ( $query );
		
		$resultTable = array ();
		for($i = 1; $i <= 12; $i ++) {
			foreach ( $this->view->years as $year ) {
				$j = $year->year;
				$date_start = date ( 'Y-m-d', mktime ( 0, 0, 0, $i, 1, $j ) );
				$date_end = date ( 'Y-m-d', mktime ( 0, 0, 0, $i + 1, 1, $j ) );
				
				$query = '
					SELECT
						SUM(total-total*percent) AS total,
						SUM(total_consult-total_consult*percent_consult) AS total_consult
						FROM
							(SELECT
								il.total_price AS total,
								i.reduction/i.total AS percent,
								IF ((p.is_consult=1),il.total_price,0) AS total_consult,
								IF ((p.is_consult=1),i.reduction/i.total,0) AS percent_consult
								FROM webshop_products AS p, ndc_invoice_lines AS il, ndc_invoices AS i
								WHERE p.id=il.product_id
								AND i.id=il.invoice_id
								AND i.status<>"open"
								AND TO_DAYS(created)>=TO_DAYS("' . $date_start . '")
								AND TO_DAYS(created)<TO_DAYS("' . $date_end . '")						
							)
						AS pp';
				$result = $db->fetchAll ( $query );
				
				$resultTable [$i] [$j] ['total'] = $result [0]->total;
				$resultTable [$i] [$j] ['consult'] = $result [0]->total_consult;
				$resultTable [$i] [$j] ['product'] = $result [0]->total - $result [0]->total_consult;
			}
		}
		
		$this->view->resultTable = $resultTable;
		$this->view->dutchMonths = array ('Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December' );
	}
	
	////////////////
	// Setup part //
	////////////////
	

	/**
	 * Allow to set invoice header/footer
	 *
	 */
	public function invoicesetupAction() {
		$invoicedetails = new Model_Table_Invoicedetails ( );
		
		if ($this->_request->isPost ()) {
			$filter1 = new Zend_Filter_StringTrim ( );
			$filter2 = new Zend_Filter_StripTags ( );
			
			$invoicedetailsRow = $invoicedetails->fetchRow ();
			$invoicedetailsRow->cash_header = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'cash_header' ) ) );
			$invoicedetailsRow->pin_header = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'pin_header' ) ) );
			$invoicedetailsRow->invoice_header = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'invoice_header' ) ) );
			$invoicedetailsRow->footer = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'footer' ) ) );
			$invoicedetailsRow->save ();
		}
		
		$this->view->invoicedetailsRow = $invoicedetails->fetchRow ();
	}
	
	/**
	 * Allow to set order header/footer
	 *
	 */
	public function ordersetupAction() {
		$orderdetails = new Model_Table_Orderdetails ( );
		
		if ($this->_request->isPost ()) {
			$filter1 = new Zend_Filter_StringTrim ( );
			$filter2 = new Zend_Filter_StripTags ( );
			
			$orderdetailsRow = $orderdetails->fetchRow ();
			$orderdetailsRow->header = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'header' ) ) );
			$orderdetailsRow->footer = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'footer' ) ) );
			$orderdetailsRow->save ();
		}
		
		$this->view->orderdetailsRow = $orderdetails->fetchRow ();
	}
	
	/**
	 * Products setup
	 *
	 */
	public function productsAction() {
	}
	
	/**
	 * Add new product
	 *
	 */
	public function productaddAction() {
		$products = new Model_Table_Products ( );
		$categories = new Model_Table_Productcategories ( );
		$suppliers = new Model_Table_Suppliers ( );
		$vat = new Model_Table_Vat ( );
		
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			if (! isset ( $userData ['is_consult'] ))
				$userData ['is_consult'] = '';
			
            $filters = array ('productcode' => array ('StringTrim', 'StripTags' ), 'name' => array ('StringTrim', 'StripTags' ), 'productcategory_id' => array ('Int' ), 'productcategory_new' => array ('StringTrim', 'StripTags' ), 'supplier_id' => array ('Int' ), 'size' => array ('StringTrim', 'StripTags' ), 'price' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ), 'is_consult' => array ('Int' ), 'consult_duration' => array (new Controller_Filter_PrepareInt ( ) ), 'description' => array ('StringTrim', 'StripTags' ), 'vat_id' => array ('Int' ) );
            $validators = array ('productcode' => array ('allowEmpty' => true ), 'name' => array ('notEmpty' ), 'productcategory_id' => array ('allowEmpty' => true ), 'productcategory_new' => array ('allowEmpty' => true ), 'supplier_id' => array ('allowEmpty' => true ), 'size' => array ('allowEmpty' => true ), 'price' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ), 'is_consult' => array ('allowEmpty' => true ), 'consult_duration' => array ('Int', 'allowEmpty' => true ), 'description' => array ('allowEmpty' => true ), 'vat_id' => array ('notEmpty' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			$userData ['name'] = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'name' ) ) );
			
			if ($input->isValid ()) {
				if (isset ( $userData ['productcategory_new'] ) && strlen ( $userData ['productcategory_new'] ) > 0) {
					$category = $categories->createRow ();
					$category->name = $userData ['productcategory_new'];
					$category->save ();
					
					$userData ['productcategory_id'] = $category->id;
				}
				unset ( $userData ['productcategory_new'] );
				
				$product = $products->createRow ( $userData );
				$product->save ();
				
				$this->_redirect ( '/admin/products/' );
				return;
			} else {
				unset ( $userData ['productcategory_new'] );
				
				$this->view->messages = $input->getMessages ();
				
				$this->view->product = new stdClass ( );
				foreach ( $userData as $key => $value )
					$this->view->product->$key = $value;
			}
		} else {
			$this->view->product = new stdClass ( );
		}
		
		$this->view->categories = $categories->fetchAll ( null, 'name' );
		$this->view->suppliers = $suppliers->fetchAll ( null, 'name' );
		$this->view->vat = $vat->fetchAll ();
	}
	
	/**
	 * Edit product data
	 *
	 */
	public function producteditAction() {
		$products = new Model_Table_Products ( );
		$categories = new Model_Table_Productcategories ( );
		$suppliers = new Model_Table_Suppliers ( );
		$vat = new Model_Table_Vat ( );
		
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		if ($this->_request->getParam ( 'id' )) {
			$product = $products->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $product instanceof Model_Table_Row_Product) {
				$this->_redirect ( '/admin/products/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/products/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			if (! isset ( $userData ['is_consult'] ))
				$userData ['is_consult'] = '';
			
            $filters = array ('productcode' => array ('StringTrim', 'StripTags' ), 'name' => array ('StringTrim', 'StripTags' ), 'productcategory_id' => array ('Int' ), 'productcategory_new' => array ('StringTrim', 'StripTags' ), 'supplier_id' => array ('Int' ), 'size' => array ('StringTrim', 'StripTags' ), 'price' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareFloat ( ) ), 'is_consult' => array ('Int' ), 'consult_duration' => array (new Controller_Filter_PrepareInt ( ) ), 'description' => array ('StringTrim', 'StripTags' ), 'vat_id' => array ('Int' ) );
            $validators = array ('productcode' => array ('allowEmpty' => true ), 'name' => array ('notEmpty' ), 'productcategory_id' => array ('allowEmpty' => true ), 'productcategory_new' => array ('allowEmpty' => true ), 'supplier_id' => array ('allowEmpty' => true ), 'size' => array ('allowEmpty' => true ), 'price' => array (new Zend_Validate_Float(array('locale' => 'us') ), 'allowEmpty' => true ), 'is_consult' => array ('allowEmpty' => true ), 'consult_duration' => array ('Int', 'allowEmpty' => true ), 'description' => array ('allowEmpty' => true ), 'vat_id' => array ('notEmpty' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			$userData ['name'] = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'name' ) ) );
			
			if ($input->isValid ()) {
				if (isset ( $userData ['productcategory_new'] ) && strlen ( $userData ['productcategory_new'] ) > 0) {
					$category = $categories->createRow ();
					$category->name = $userData ['productcategory_new'];
					$category->save ();
					
					$userData ['productcategory_id'] = $category->id;
				}
				unset ( $userData ['productcategory_new'] );
				
				$product->setFromArray ( $userData );
				$product->save ();
				
				$this->_redirect ( '/admin/products/' );
				return;
			} else {
				unset ( $userData ['productcategory_new'] );
				
				$this->view->messages = $input->getMessages ();
				
				$this->view->product = $product;
				foreach ( $userData as $key => $value )
					$this->view->product->$key = $value;
			}
		} else {
			$this->view->product = $product;
		}
		
		$this->view->categories = $categories->fetchAll ( null, 'name' );
		$this->view->suppliers = $suppliers->fetchAll ( null, 'name' );
		$this->view->vat = $vat->fetchAll ();
	}
	
	/**
	 * Delete product
	 *
	 */
	public function productdelAction() {
		$products = new Model_Table_Products ( );
		if ($this->_request->getParam ( 'id' )) {
			$product = $products->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $product instanceof Model_Table_Row_Product) {
				$this->_redirect ( '/admin/products/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/products/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			if (count ( $product->findDependentRowset ( 'Model_Table_Consults' ) ) > 0 || count ( $product->findDependentRowset ( 'Model_Table_Invoicelines' ) ) > 0 || count ( $product->findDependentRowset ( 'Model_Table_Shoporderlines' ) ) > 0) {
				$product->active = 0;
				$product->save ();
			} else
				$product->delete ();
		}
		
		$this->_redirect ( '/admin/products/' );
	}
	
	/**
	 * Suppliers setup
	 *
	 */
	public function suppliersAction() {
	}
	
	/**
	 * Add new supplier
	 *
	 */
	public function supplieraddAction() {
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			
			$filters = array ('name' => array ('StringTrim', 'StripTags' ), 'email' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('name' => array ('notEmpty' ), 'email' => array ('notEmpty', 'EmailAddress' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			
			if ($input->isValid ()) {
				$suppliers = new Model_Table_Suppliers ( );
				$supplier = $suppliers->createRow ( $userData );
				$supplier->save ();
				
				$this->_redirect ( '/admin/suppliers/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();
				
				$this->view->supplier = new stdClass ( );
				foreach ( $userData as $key => $value )
					$this->view->supplier->$key = $value;
			}
		} else {
			$this->view->supplier = new stdClass ( );
		}
	}
	
	/**
	 * Edit supplier data
	 *
	 */
	public function suppliereditAction() {
		$suppliers = new Model_Table_Suppliers ( );
		
		if ($this->_request->getParam ( 'id' )) {
			$supplier = $suppliers->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $supplier instanceof Model_Table_Row_Supplier) {
				$this->_redirect ( '/admin/suppliers/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/suppliers/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			
			$filters = array ('name' => array ('StringTrim', 'StripTags' ), 'email' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('name' => array ('notEmpty' ), 'email' => array ('notEmpty', 'EmailAddress' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			
			if ($input->isValid ()) {
				$supplier->setFromArray ( $userData );
				$supplier->save ();
				
				$this->_redirect ( '/admin/suppliers/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();
				
				$this->view->supplier = $supplier;
				foreach ( $userData as $key => $value )
					$this->view->supplier->$key = $value;
			}
		} else {
			$this->view->supplier = $supplier;
		}
	}
	
	/**
	 * Delete supplier
	 *
	 */
	public function supplierdelAction() {
		$suppliers = new Model_Table_Suppliers ( );
		if ($this->_request->getParam ( 'id' )) {
			$supplier = $suppliers->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $supplier instanceof Model_Table_Row_Supplier) {
				$this->_redirect ( '/admin/suppliers/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/suppliers/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$products = new Model_Table_Products ( );
			$products->update ( array ('supplier_id' => 0 ), 'supplier_id=' . $supplier->id );
			
			if (count ( $supplier->findDependentRowset ( 'Model_Table_Orders' ) ) > 0) {
				$supplier->active = 0;
				$supplier->save ();
			} else
				$supplier->delete ();
		}
		
		$this->_redirect ( '/admin/suppliers/' );
	}
	
	/**
	 * Employees setup
	 *
	 */
	public function employeesAction() {
	}
	
	/**
	 * Add new employee
	 *
	 */
	public function employeeaddAction() {
		$users = new Model_Table_Users ( );
		
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['employee_locations'] );
			
			$filters = array ('name' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('name' => array ('notEmpty' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			
			if ($input->isValid ()) {
				$employees = new Model_Table_Employees ( );
				$employeeslocations = new Model_Table_Employeeslocations ( );
				
				$employee = $employees->createRow ( $userData );
				$employee->save ();
				
				foreach ( $this->_request->getPost ( 'employee_locations' ) as $locationRow ) {
					$location_id = ( int ) $locationRow;
					
					if ($location_id) {
						$elRow = $employeeslocations->createRow ();
						$elRow->employee_id = $employee->id;
						$elRow->location_id = $location_id;
						$elRow->save ();
					}
				}
				
				$this->_redirect ( '/admin/employees/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();
				
				$this->view->employee_locations = $this->_request->getPost ( 'employee_locations' );
				
				$this->view->employee = new stdClass ( );
				foreach ( $userData as $key => $value )
					$this->view->employee->$key = $value;
			}
		} else {
			$this->view->employee_locations = array ();
			$this->view->employee = new stdClass ( );
		}
		
		$this->view->locationsToShow = $users->getLocations ();
	}
	
	/**
	 * Edit employee data
	 *
	 */
	public function employeeeditAction() {
		$employees = new Model_Table_Employees ( );
		$users = new Model_Table_Users ( );
		
		if ($this->_request->getParam ( 'id' )) {
			$employee = $employees->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $employee instanceof Model_Table_Row_Employee) {
				$this->_redirect ( '/admin/employees/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/employees/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			
			$filters = array ('name' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('name' => array ('notEmpty' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			
			if ($input->isValid ()) {
				$employeeslocations = new Model_Table_Employeeslocations ( );
				
				$employee->setFromArray ( $userData );
				$employee->save ();
				
				$employeeslocations->delete ( 'employee_id=' . $employee->id );
				
				foreach ( $this->_request->getPost ( 'employee_locations' ) as $locationRow ) {
					$location_id = ( int ) $locationRow;
					
					if ($location_id) {
						$elRow = $employeeslocations->createRow ();
						$elRow->employee_id = $employee->id;
						$elRow->location_id = $location_id;
						$elRow->save ();
					}
				}
				
				$this->_redirect ( '/admin/employees/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();
				
				$this->view->employee_locations = $this->_request->getPost ( 'employee_locations' );
				
				$this->view->employee = new stdClass ( );
				foreach ( $userData as $key => $value )
					$this->view->employee->$key = $value;
			}
		} else {
			$this->view->employee = $employee;
			
			$employee_locations = array ();
			foreach ( $employee->findDependentRowset ( 'Model_Table_Employeeslocations', 'Employee' ) as $elRow )
				$employee_locations [] = $elRow->location_id;
			$this->view->employee_locations = $employee_locations;
		}
		
		$this->view->locationsToShow = $users->getLocations ();
	}
	
	/**
	 * Delete employee
	 *
	 */
	public function employeedelAction() {
		$employees = new Model_Table_Employees ( );
		if ($this->_request->getParam ( 'id' )) {
			$employee = $employees->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $employee instanceof Model_Table_Row_Employee) {
				$this->_redirect ( '/admin/employees/' );
				return;
			}
		} else {
			$this->_redirect ( '/admin/employees/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			if (count ( $employee->findDependentRowset ( 'Model_Table_Consults' ) ) > 0 || count ( $employee->findDependentRowset ( 'Model_Table_Invoices' ) ) > 0) {
				$appointments = new Model_Table_Appointments ( );
				$appointments->delete ( 'employee_id=' . $employee->id );
				
				$employee->active = 0;
				$employee->save ();
			} else
				$employee->delete ();
		}
		
		$this->_redirect ( '/admin/employees/' );
	}

}
