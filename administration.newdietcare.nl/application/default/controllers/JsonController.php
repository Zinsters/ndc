<?php

/**
 * Class Default_JsonController
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

//class Default_JsonController extends Zend_Controller_Action {
class Default_JsonController extends Controller_Json {
	
	//////////////
	// Get data //
	//////////////
	

	/**
	 * Returns JSON representation of difference in customer weight
	 * 
	 * @return JSON 
	 */
	public function weightdifferenceAction() {
		$filter = new Controller_Filter_PrepareFloat ( );
		$filter1 = new Controller_Filter_PrintFloat ( );
		
		$users = new Model_Table_Users ( );
		$customer = $users->getById ( ( int ) $this->_request->getParam ( 'id' ) );
		if ($customer instanceof Model_Table_Row_User) {
			$currentWeight = ( float ) $filter->filter ( $this->_request->getParam ( 'weight' ) );
			if ($currentWeight > 0 && $customer->getRealStartWeight () > 0) {
				if ($customer->getRealStartWeight () >= $currentWeight)
					$result = 'Totaal gewichtsverlies: ' . $filter1->filter ( $customer->getRealStartWeight () - $currentWeight ) . ' kg';
				else
					$result = 'Totaal gewichtstoename: ' . $filter1->filter ( $currentWeight - $customer->getRealStartWeight () ) . ' kg';
			} else
				$result = '';
		} else {
			$result = '';
		}
		$response = new stdClass ( );
		$response->weightDifference = $result;
		
		echo Zend_Json::encode ( $response );
		exit ();
	}
	
	/**
	 * Return the set of products
	 *
	 * @return JSON 
	 */
	public function productssearchAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareFloat ( );
		$filter4 = new Controller_Filter_PrintFloat ( 2 );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'productcode' ))
			$params->productcode = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'productcode' ) ) );
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'category' ))
			$params->category = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'category' ) ) );
		if ($this->_request->getParam ( 'size' ))
			$params->size = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'size' ) ) );
		if ($this->_request->getParam ( 'price' ))
			$params->price = ( float ) $filter3->filter ( $this->_request->getParam ( 'price' ) );
		if ($this->_request->getParam ( 'supplier' ))
			$params->supplier = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'supplier' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'productcode', 'category', 'size', 'price', 'supplier' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'productcode';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$requestType = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'type' ) ) );
		if ($requestType == 'orders')
			$params->is_consult = 0;
		
		$products = new Model_Table_Products ( );
		$allData = $products->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $products->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$responce->rows [$i] ['id'] = $row->id;
				
				if ($requestType == 'orders')
					$responce->rows [$i] ['cell'] = array (htmlentities ( $row->productcode ), $this->view->escape ( $row->name ), htmlentities ( $row->category ), htmlentities ( $row->size ), htmlentities ( $row->supplier ) );
				else
					$responce->rows [$i] ['cell'] = array (htmlentities ( $row->productcode ), $this->view->escape ( $row->name ), htmlentities ( $row->category ), htmlentities ( $row->size ), '&#0128;', $filter4->filter ( $row->price_consumer_incl_vat ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' );
				
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return found customers
	 *
	 * @return JSON 
	 */
	public function customerssearchAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareDate ( );
		$filter4 = new Controller_Filter_PrintDate ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'achternaam' ))
			$params->achternaam = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'achternaam' ) ) );
		if ($this->_request->getParam ( 'voornaam' ))
			$params->voornaam = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'voornaam' ) ) );
		if ($this->_request->getParam ( 'geboortedatum' ))
			$params->geboortedatum = $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'geboortedatum' ) ) ) );
		if ($this->_request->getParam ( 'thuisadres' ))
			$params->thuisadres = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'thuisadres' ) ) );
		if ($this->_request->getParam ( 'thuisplaats' ))
			$params->thuisplaats = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'thuisplaats' ) ) );
		if ($this->_request->getParam ( 'thuispostcode' ))
			$params->thuispostcode = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'thuispostcode' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('achternaam', 'voornaam', 'tussenvoegsel', 'geboortedatum', 'thuisadres', 'thuisplaats', 'thuispostcode', 'email' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'achternaam';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$users = new Model_Table_Users ( );
		$invoices = new Model_Table_Invoices ( );
		$shoporders = new Model_Table_Shoporders ( );
		$allData = $users->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $users->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = (string) $params->page;
			$responce->total = (string) ( $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0 );
			$responce->records = (string) count ( $allData );
			$i = 0;
			
			$select = $invoices->select ();
			$select->from ( $invoices, array ('COUNT(*) as count' ) );
			$select2 = $shoporders->select ();
			$select2->from ( $shoporders, array ('COUNT(*) as count' ) );
			foreach ( $sampleData as $row ) {
				$select->reset ( Zend_Db_Select::WHERE );
				$select->where ( 'customer_id=' . $row->userid );
				$count = $invoices->fetchAll ( $select )->current ()->count;
				$select2->reset ( Zend_Db_Select::WHERE );
				$select2->where ( 'userid=' . $row->userid );
				$count2 = $shoporders->fetchAll ( $select2 )->current ()->count;
				if (! $count && ! $count2) {
					$buttonString = '
					<form method="post"
					action="' . $this->_request->getBaseUrl () . '/customer/delete/"
					onsubmit="return confirm(' . "'" . 'klant verwijderen?' . "'" . ')"><input type="submit"
					class="delete" value="" title="verwijder" onclick="stopSelect=true;"><input type="hidden"
					name="id" value="' . $row->userid . '"></form>
				';
				}

				$responce->rows [$i] ['id'] = $row->userid;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->achternaam ), htmlentities ( $row->voornaam ), htmlentities ( $row->tussenvoegsel ), $filter4->filter( $row->geboortedatum ), htmlentities ( $row->thuisadres ), htmlentities ( $row->thuisplaats ), htmlentities ( $row->thuispostcode ), htmlentities ( $row->email ), ! $count && ! $count2 ? $buttonString : '' );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return found invoices
	 *
	 * @return JSON 
	 */
	public function invoicessearchAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareDate ( );
		$filter4 = new Controller_Filter_PrepareFloat ( );
		$filter5 = new Controller_Filter_PrintFloat ( 2 );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'employee_id' ))
			$params->employeeId = ( int ) $this->_request->getParam ( 'employee_id' );
		if ($this->_request->getParam ( 'location_id' ))
			$params->locationId = ( int ) $this->_request->getParam ( 'location_id' );
		if ($this->_request->getParam ( 'paymentmethod' ))
			$params->paymentmethod = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'paymentmethod' ) ) );
		if ($this->_request->getParam ( 'date_start' ))
			$params->date_start = $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'date_start' ) ) ) );
		if ($this->_request->getParam ( 'date_end' ))
			$params->date_end = $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'date_end' ) ) ) );
		if ($this->_request->getParam ( 'price_start' ))
			$params->price_start = $filter4->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'price_start' ) ) ) );
		if ($this->_request->getParam ( 'price_end' ))
			$params->price_end = $filter4->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'price_end' ) ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('date', 'customer', 'employee', 'location', 'totalwithoutreduction', 'paymentmethod' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'date';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			if ($params->order == 'date')
				$params->direction = 'DESC';
			else
				$params->direction = 'ASC';
		}
		
		$invoices = new Model_Table_Invoices ( );
		$allData = $invoices->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $invoices->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '';
				if ($row->status == 'final' || $row->status == 'payed') {
					$buttonString .= '<form action="' . $this->_request->getBaseUrl () . '/admin/invoicepdf/id/' . $row->id . '/" target="_blank"><input type="submit" class="pdf" value=""	title="print"></form> ';
				}
				if ($row->paymentmethod == 'bank' && $row->status == 'final') {
					$buttonString .= '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/invoicepayed/id/' . $row->id . '/" onsubmit="return confirm(' . "'" . 'betaling per bank &#0128; ' . $filter5->filter ( $row->totalwithoutreduction ) . '?' . "'" . ')"><input type="submit" class="pay" value="" title="bevestig betaling"></form> ';
				}
				//if (! $row->burned) {
				$buttonString .= '<form	action="' . $this->_request->getBaseUrl () . '/admin/invoice/id/' . $row->id . '/"><input type="submit" class="edit" value="" title="bewerk"></form> ';
				//}
				if ($row->status == 'open') {
					$buttonString .= '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/invoicedel/id/' . $row->id . '/" onsubmit="return confirm(' . "'" . 'rekening verwijderen?' . "'" . ')"><input type="submit" class="delete" value="" title="verwijder"></form> ';
				}
				
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array ($row->date, htmlentities ( $row->customer ), htmlentities ( $row->employee ), htmlentities ( $row->location ), '&#0128; ' . $row->totalwithoutreduction, $row->paymentmethod, $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Find the products by defined code
	 * Return nothing if there are more than one product found
	 *
	 * @return JSON 
	 */
	public function getproductbycodeAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$responce = new stdClass ( );
		$responce->id = '';
		$responce->code = '';
		$responce->name = '';
		$responce->price = '';
		
		if ($this->_request->getParam ( 'productcode' )) {
			$params = new stdClass ( );
			$params->productcode = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'productcode' ) ) );
			$params->order = 'productcode';
			$params->direction = 'ASC';
			$products = new Model_Table_Products ( );
			$data = $products->getByRequest ( $params );
			
			if (count ( $data ) == 1) {
				$responce->id = $data->current ()->id;
				$responce->code = $data->current ()->productcode;
				$responce->name = $data->current ()->name;
				$responce->supplier_id = ( int ) $data->current ()->supplier_id;
				$responce->price = $data->current ()->price_consumer_incl_vat;
				
				if ($data->current ()->supplier_id) {
					$suppliers = new Model_Table_Suppliers ( );
					$responce->supplier = $suppliers->getById ( $data->current ()->supplier_id )->name;
				} else {
					$responce->supplier = 'No supplier';
				}
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Find the product by defined id
	 *
	 * @return JSON 
	 */
	public function getproductbyidAction() {
		$responce = new stdClass ( );
		$responce->id = '';
		$responce->code = '';
		$responce->name = '';
		$responce->price = '';
		
		if ($this->_request->getParam ( 'id' )) {
			$products = new Model_Table_Products ( );
			$data = $products->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			
			if ($data) {
				$responce->id = $data->id;
				$responce->code = $data->productcode;
				$responce->name = $data->name;
				$responce->supplier_id = ( int ) $data->supplier_id;
				$responce->price = $data->price_consumer_incl_vat;
				
				if ($data->supplier_id) {
					$suppliers = new Model_Table_Suppliers ( );
					$responce->supplier = $suppliers->getById ( $data->supplier_id )->name;
				} else {
					$responce->supplier = 'No supplier';
				}
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return content of agenda lightbox
	 * 
	 * @return html string
	 */
	public function getmodalcontentAction() {
		$result = '';
		
		if ($this->_request->getPost ( 'id' )) {
			$appointments = new Model_Table_Appointments ( );
			$appointment = $appointments->getById ( ( int ) $this->_request->getPost ( 'id' ) );
			
			if ($appointment instanceof Model_Table_Row_Appointment) {
				$customer = $appointment->getCustomer ();
				$location = $appointment->getLocation ();
				$employee = $appointment->getEmployee ();
				
				$timeParts = explode ( ':', substr ( $appointment->time_end, 0, 5 ) );
				$time = mktime ( $timeParts [0], $timeParts [1] );
				$time = $time + 900;
				$realEnd = date ( "H:i", $time );
				
				$result = '
					<form>
					<div class="appointment_customer"><a href="' . $this->_request->getBaseUrl () . '/customer/view/id/' . $customer->userid . '/">' . ($customer instanceof Model_Table_Row_User ? $customer->getName () : '') . '</a></div>
					<div class="appointment_location">' . ($location instanceof Model_Table_Row_user ? $location->bedrijfsnaam : '') . '</div>
					<div class="appointment_employee">' . ($employee instanceof Model_Table_Row_Employee ? $employee->name : '') . '</div>
					<div class="appointment_time"><input type="text" id="appointmentTimeStart"
						class="appointment_field" value="' . substr ( $appointment->time_start, 0, 5 ) . '" /> <input type="text" id="appointmentTimeEnd"
						class="appointment_field" value="' . $realEnd . '" /></div>
					<textarea id="appointmentNote" class="appointment_note">' . (htmlentities ( $appointment->notes )) . '</textarea><br />
					<div id="colorpicker"></div>
					<div class="appointment_buttons">
						<input type="submit" value="OK" onClick="appointmentEdit(' . $appointment->id . ');" />
						<input type="submit" value="Verwijder" onClick="appointmentDelete(' . $appointment->id . ');" /></form>
					</div>
					<input type="hidden" value="' . $appointment->style . '" id="appointmentStyle" />
					<br />
				';
			}
		}
		
		echo $result;
		exit ();
	}
	
	/**
	 * Format values of appointmentTimeStart and appointmentTimeEnd fields
	 *
	 * @return JSON 
	 */
	public function gettimeendAction() {
		$responce = new stdClass ( );
		
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareTime ( );
		
		$timeStart = $this->checkTime ( $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'appointmentTimeStart' ) ) ) ) );
		$timeEnd = $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'appointmentTimeEnd' ) ) ) );
		
		if ($timeStart) {
			$responce->timeStart = $timeStart;
			$result = '';
			foreach ( $this->getTimeEndArray ( $responce->timeStart ) as $time )
				$result .= '<option' . ($time == $timeEnd ? ' selected' : '') . '>' . $time . '</option>';
			$responce->timeEnd = $result;
		} else {
			$responce->timeStart = '';
			$responce->timeEnd = '';
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return top products list
	 *
	 */
	public function productstopAction() {
		if ($this->_request->getParam ( 'filter_employee' ) || $this->_request->getParam ( 'filter_location' ) || $this->_request->getParam ( 'filter_date_start' ) || $this->_request->getParam ( 'filter_date_end' )) {
			$date = $this->_request->getParam ( 'filter_date_start' );
			$dateParts = explode( '-', $date );
			$date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
			$this->_request->setParam( 'filter_date_start', $date );

			$date = $this->_request->getParam ( 'filter_date_end' );
			$dateParts = explode( '-', $date );
			$date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
			$this->_request->setParam( 'filter_date_end', $date );

			$userData = $this->_request->getParams ();
			
/*
			$filters = array ('filter_employee' => array ('Int' ), 'filter_location' => array ('Int' ), 'filter_date_start' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ), 'filter_date_end' => array ('StringTrim', 'StripTags', new Controller_Filter_PrepareDate ( ) ) );
			
			$validators = array ('filter_employee' => array ('Int', 'allowEmpty' => true ), 'filter_location' => array ('Int', 'allowEmpty' => true ), 'filter_date_start' => array ('Date', 'allowEmpty' => true ), 'filter_date_end' => array ('Date', 'allowEmpty' => true ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
*/

			$input = new stdClass ( );
			if ( $userData[ 'filter_employee' ] != '' ) {
				$input->filter_employee = $userData[ 'filter_employee' ];
			}
			if ( $userData[ 'filter_location' ] != '' ) {
				$input->filter_location = $userData[ 'filter_location' ];
			}
			$input->filter_date_start = $userData[ 'filter_date_start' ];
			$input->filter_date_end = $userData[ 'filter_date_end' ];
		} else {
			$input = new stdClass ( );
			$input->filter_date_start = date ( 'Y-m-d' );
			$input->filter_date_end = $input->filter_date_start;
		}
		
		$params = new stdClass ( );
		
		if ( isset( $input->filter_employee ) )
			$params->employeeId = $input->filter_employee;
		if ( isset( $input->filter_location ) )
			$params->locationId = $input->filter_location;
		if ( isset( $input->filter_date_start ) )
			$params->date_start = $input->filter_date_start;
		if ( isset( $input->filter_date_end ) )
			$params->date_end = $input->filter_date_end;
		
		$products = new Model_Table_Products ( );
		$allData = $products->getProductsTop ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $products->getProductsTop ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->productcode ), $this->view->escape ( $row->name ), $row->number_total, '&#0128;', $row->total_price_total . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of products
	 *
	 * @return JSON 
	 */
	public function productssetupAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareFloat ( );
		$filter4 = new Controller_Filter_PrintFloat ( 2 );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'productcode' ))
			$params->productcode = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'productcode' ) ) );
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'category' ))
			$params->category = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'category' ) ) );
		if ($this->_request->getParam ( 'size' ))
			$params->size = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'size' ) ) );
		if ($this->_request->getParam ( 'price' ))
			$params->price = ( float ) $filter3->filter ( $this->_request->getParam ( 'price' ) );
		if ($this->_request->getParam ( 'supplier' ))
			$params->supplier = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'supplier' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'productcode', 'category', 'size', 'price', 'supplier' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'productcode';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$products = new Model_Table_Products ( );
		$allData = $products->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $products->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '
					<form method="post"
					action="' . $this->_request->getBaseUrl () . '/admin/productdel/"
					onsubmit="return confirm(' . "'" . 'product verwijderen?' . "'" . ')"><input type="submit"
					class="delete" value="" title="verwijder"><input type="hidden"
					name="id" value="' . $row->id . '"></form><form
					action="' . $this->_request->getBaseUrl () . '/admin/productedit/id/' . $row->id . '/"><input type="submit"
					class="edit" value=""></form>
				';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->productcode ), $this->view->escape ( $row->name ), htmlentities ( $row->category ), htmlentities ( $row->size ), '&#0128;', $filter4->filter ( $row->price_consumer_incl_vat ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', htmlentities ( $row->supplier ), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of suppliers
	 *
	 * @return JSON 
	 */
	public function supplierssetupAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'email' ))
			$params->email = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'email' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'email' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'name';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$suppliers = new Model_Table_Suppliers ( );
		$allData = $suppliers->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $suppliers->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '
					<form method="post"
					action="' . $this->_request->getBaseUrl () . '/admin/supplierdel/"
					onsubmit="return confirm(' . "'" . 'leverancier verwijderen?' . "'" . ')"><input type="submit"
					class="delete" value="" title="verwijder"><input type="hidden"
					name="id" value="' . $row->id . '"></form><form
					action="' . $this->_request->getBaseUrl () . '/admin/supplieredit/id/' . $row->id . '/"><input type="submit"
					class="edit" value=""></form>
				';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->name ), htmlentities ( $row->email ), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of employees
	 *
	 * @return JSON 
	 */
	public function employeessetupAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		
		$params->order = 'name';
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$employees = new Model_Table_Employees ( );
		$allData = $employees->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $employees->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '
					<form method="post"
					action="' . $this->_request->getBaseUrl () . '/admin/employeedel/"
					onsubmit="return confirm(' . "'" . 'delete employee?' . "'" . ')"><input type="submit"
					class="delete" value="" title="verwijder"><input type="hidden"
					name="id" value="' . $row->id . '"></form><form
					action="' . $this->_request->getBaseUrl () . '/admin/employeeedit/id/' . $row->id . '/"><input type="submit"
					class="edit" value=""></form>
				';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->name ), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of locations
	 *
	 * @return JSON 
	 */
	public function locationssetupAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'deliveryaddress1' ))
			$params->deliveryaddress1 = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'deliveryaddress1' ) ) );
		if ($this->_request->getParam ( 'deliveryaddress2' ))
			$params->deliveryaddress2 = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'deliveryaddress2' ) ) );
		if ($this->_request->getParam ( 'city' ))
			$params->city = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'city' ) ) );
		if ($this->_request->getParam ( 'zip' ))
			$params->zip = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'zip' ) ) );
		if ($this->_request->getParam ( 'email' ))
			$params->email = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'email' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'deliveryaddress1', 'deliveryaddress2', 'city', 'zip', 'email' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'name';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$locations = new Model_Table_Locations ( );
		$allData = $locations->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $locations->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '
					<form method="post"
					action="' . $this->_request->getBaseUrl () . '/admin/locationdel/"
					onsubmit="return confirm(' . "'" . 'locatie verwijderen?' . "'" . ')"><input type="submit"
					class="delete" value="" title="verwijder"><input type="hidden"
					name="id" value="' . $row->id . '"></form><form
					action="' . $this->_request->getBaseUrl () . '/admin/locationedit/id/' . $row->id . '/"><input type="submit"
					class="edit" value=""></form>
				';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array (htmlentities ( $row->name ), htmlentities ( $row->deliveryaddress1 ), htmlentities ( $row->deliveryaddress2 ), htmlentities ( $row->city ), htmlentities ( $row->zip ), htmlentities ( $row->email ), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of news
	 *
	 * @return JSON 
	 */
	public function newsAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		$filter3 = new Controller_Filter_PrepareDate ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'title' ))
			$params->title = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'title' ) ) );
		if ($this->_request->getParam ( 'date' ))
			$params->date = $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'date' ) ) ) );
		if ($this->_request->getParam ( 'published' ) === "0" || $this->_request->getParam ( 'published' ) === "1")
			$params->published = ( int ) $this->_request->getParam ( 'published' );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('title', 'date', 'published' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'date';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			if ($params->order == 'date')
				$params->direction = 'DESC';
			else
				$params->direction = 'ASC';
		}
		
		$news = new Model_Table_News ( );
		$allData = $news->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $news->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/newspublish/id/' . $row->id . '/"><input type="submit" class="' . ($row->published ? 'unpublish' : 'publish') . '" value="" title="' . ($row->published ? 'unpublish' : 'publish') . '"></form>';
				$buttonString .= '<form action="' . $this->_request->getBaseUrl () . '/admin/newsedit/id/' . $row->id . '/"><input type="submit" class="edit" value="" title="edit"></form><form method="post" action="' . $this->_request->getBaseUrl () . '/admin/newsdel/" onsubmit="return confirm(' . "'" . 'delete news item?' . "'" . ')"><input type="submit" class="delete" value="" title="verwijder"><input type="hidden" name="id" value="' . $row->id . '"></form>';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array ($this->view->escape ( $row->title ), $this->view->escape ( $row->date ), ($row->published ? 'Ja' : 'Nee'), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of test subscribers
	 *
	 * @return JSON 
	 */
	public function testsubscribersAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		$params->group_id = 2;
		
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'email' ))
			$params->email = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'email' ) ) );
		if ($this->_request->getParam ( 'active' ) === "0" || $this->_request->getParam ( 'active' ) === "1")
			$params->active = ( int ) $this->_request->getParam ( 'active' );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'email', 'active' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'name';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$testsubscribers = new Model_Table_Testsubscribers ( );
		$allData = $testsubscribers->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $testsubscribers->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/testsubscriberactive/id/' . $row->id . '/"><input type="submit" class="' . ($row->active ? 'unpublish' : 'publish') . '" value="" title="' . ($row->active ? 'make inactive' : 'make active') . '"></form>';
				$buttonString .= '<form action="' . $this->_request->getBaseUrl () . '/admin/testsubscriberedit/id/' . $row->id . '/"><input type="submit" class="edit" value="" title="edit"></form><form method="post" action="' . $this->_request->getBaseUrl () . '/admin/testsubscriberdel/" onsubmit="return confirm(' . "'" . 'delete subscriber?' . "'" . ')"><input type="submit" class="delete" value="" title="verwijder"><input type="hidden" name="id" value="' . $row->id . '"></form>';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array ($this->view->escape ( $row->name ), $this->view->escape ( $row->email ), ($row->active ? 'Ja' : 'Nee'), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the set of groups
	 *
	 * @return JSON 
	 */
	public function groupsAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'name';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$groups = new Model_Table_Subscriptiongroups ( );
		$allData = $groups->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $groups->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '<form action="' . $this->_request->getBaseUrl () . '/admin/groupedit/id/' . $row->id . '/"><input type="submit" class="edit" value="" title="edit"></form>';
				if ($row->id != 1 && $row->id != 2)
					$buttonString .= '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/groupdel/" onsubmit="return confirm(' . "'" . 'delete group?' . "'" . ')"><input type="submit" class="delete" value="" title="verwijder"><input type="hidden" name="id" value="' . $row->id . '"></form>';
				
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array ($this->view->escape ( $row->name ), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/**
	 * Return the list of subscribers by subscription groups
	 *
	 * @return JSON 
	 */
	public function subscribersAction() {
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		$params = new stdClass ( );
		
		if ($this->_request->getParam ( 'group_id' ))
			$params->group_id = ( int ) $this->_request->getParam ( 'group_id' );
		if ($this->_request->getParam ( 'name' ))
			$params->name = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'name' ) ) );
		if ($this->_request->getParam ( 'email' ))
			$params->email = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'email' ) ) );
		if ($this->_request->getParam ( 'active' ) === "0" || $this->_request->getParam ( 'active' ) === "1")
			$params->active = ( int ) $this->_request->getParam ( 'active' );
		
		$order = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sidx' ) ) );
		if (in_array ( $order, array ('name', 'email', 'active', 'group_id' ) )) {
			$params->order = $order;
		} else {
			$params->order = 'name';
		}
		$direction = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'sord' ) ) );
		if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
			$params->direction = strtoupper ( $direction );
		} else {
			$params->direction = 'ASC';
		}
		
		$testsubscribers = new Model_Table_Testsubscribers ( );
		$allData = $testsubscribers->getByRequest ( clone $params );
		
		$params->page = ( int ) $this->_request->getParam ( 'page' );
		$params->rows = ( int ) $this->_request->getParam ( 'rows' );
		
		$sampleData = $testsubscribers->getByRequest ( clone $params );
		$responce = new stdClass ( );
		
		if (count ( $sampleData ) > 0) {
			$responce->page = $params->page;
			$responce->total = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
			$responce->records = count ( $allData );
			$i = 0;
			foreach ( $sampleData as $row ) {
				$buttonString = '<form method="post" action="' . $this->_request->getBaseUrl () . '/admin/subscriberactive/id/' . $row->id . '/"><input type="submit" class="' . ($row->active ? 'unpublish' : 'publish') . '" value="" title="' . ($row->active ? 'make inactive' : 'make active') . '"></form>';
				$buttonString .= '<form action="' . $this->_request->getBaseUrl () . '/admin/subscriberedit/id/' . $row->id . '/"><input type="submit" class="edit" value="" title="edit"></form><form method="post" action="' . $this->_request->getBaseUrl () . '/admin/subscriberdel/" onsubmit="return confirm(' . "'" . 'delete subscriber?' . "'" . ')"><input type="submit" class="delete" value="" title="verwijder"><input type="hidden" name="id" value="' . $row->id . '"></form>';
				$responce->rows [$i] ['id'] = $row->id;
				$responce->rows [$i] ['cell'] = array ($this->view->escape ( $row->name ), $this->view->escape ( $row->email ), ($row->active ? 'Ja' : 'Nee'), $buttonString );
				$i ++;
			}
		}
		
		echo Zend_Json::encode ( $responce );
		exit ();
	}
	
	/////////////////
	// Change data //
	/////////////////
	

	/**
	 * Generate agenda table
	 * $_POST['action']=='add': Add appointment
	 * $_POST['action']=='edit': Save data
	 * $_POST['action']=='delete': Delete appointment  
	 *
	 * @return html string
	 */
	public function getagendatableAction() {
		$appointments = new Model_Table_Appointments ( );
		$users = new Model_Table_Users ( );
		$employees = new Model_Table_Employees ( );
		
		$filter1 = new Zend_Filter_StringTrim ( );
		$filter2 = new Zend_Filter_StripTags ( );
		
		if ($this->_request->getPost ( 'action' ) == 'add') {
			$userData = $this->_request->getPost ();
			unset ( $userData ['action'] );
			
			$filters = array ('customer_id' => array ('Int' ), 'location_id' => array ('Int' ), 'employee_id' => array ('Int' ), 'date' => array ('StringTrim', 'StripTags' ), 'time_start' => array ('StringTrim', 'StripTags' ), 'time_end' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('customer_id' => array ('notEmpty' ), 'location_id' => array ('notEmpty' ), 'employee_id' => array ('notEmpty' ), 'date' => array ('notEmpty', 'Date' ), 'time_start' => array ('notEmpty' ), 'time_end' => array ('notEmpty' ) );
			
			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			
			if ($input->isValid ()) {
				if ($appointments->periodIsFree ( $userData ['employee_id'], $userData ['date'], $userData ['time_start'], $userData ['time_end'] )) {
					$appointment = $appointments->createRow ( $userData );
					$appointment->save ();
				}
			}
		} elseif ($this->_request->getPost ( 'action' ) == 'edit') {
			$filter3 = new Controller_Filter_PrepareTime ( );
			
			$appointment = $appointments->getById ( ( int ) $this->_request->getPost ( 'id' ) );
			if ($appointment instanceof Model_Table_Row_Appointment) {
				$customer = $appointment->getCustomer ();
				if ($customer instanceof Model_Table_Row_User) {
					$timeStart = $this->checkTime ( $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'appointmentTimeStart' ) ) ) ) );
					$timeEnd = $this->checkTime ( $filter3->filter ( $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'appointmentTimeEnd' ) ) ) ) );
					
					if ($timeStart && $timeEnd) {
						$timeParts_ = explode ( ':', $timeStart );
						$time_ = mktime ( $timeParts_ [0], $timeParts_ [1] );
						$timeParts = explode ( ':', $timeEnd );
						$time = mktime ( $timeParts [0], $timeParts [1] );
						$time = $time - 900;
						if ($time >= $time_) {
							$realEnd = date ( "H:i", $time );
							
							if ($appointments->periodIsFree ( $appointment->employee_id, $appointment->date, $timeStart, $realEnd, $appointment->id )) {
								$colors = array ('#d0d7dd' => 0, '#ffc1ab' => 1, '#c3ffba' => 2, '#c9e6ff' => 3 );
								
								$appointment->time_start = $timeStart;
								$appointment->time_end = $realEnd;
								$appointment->notes = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'appointmentNote' ) ) );
								$color = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'appointmentStyle' ) ) );
								$appointment->style = (isset ( $colors [$color] ) ? $colors [$color] : 0);
								$appointment->save ();
							}
						}
					}
				}
			}
		} elseif ($this->_request->getPost ( 'action' ) == 'delete') {
			$appointment = $appointments->getById ( ( int ) $this->_request->getPost ( 'id' ) );
			if ($appointment instanceof Model_Table_Row_Appointment)
				$appointment->delete ();
		}
		
		$locationId = ( int ) $this->_request->getParam ( 'location' );
		$date = $filter1->filter ( $filter2->filter ( $this->_request->getParam ( 'currentdate' ) ) );
		
		if ($locationId && $date) {
			$employeesInLocation = $employees->getByLocationId ( $locationId );
			
			$current = time ();
			$day = date ( "j", $current );
			$month = date ( "n", $current );
			$year = date ( "Y", $current );
			$hours = date ( "G", $current );
			$minutes = date ( "i", $current );
			$dateParts = explode ( '-', $date );
			$hidePast = false;
			
			$result = '<tr><th class="corner">';
			if (mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] ) == mktime ( 0, 0, 0, date ( "m", $current ), date ( "d", $current ), date ( "Y", $current ) )) {
				$defaultNamespace = new Zend_Session_Namespace ( 'default' );
				if ($this->_request->getParam ( 'past' )) {
					if ($this->_request->getParam ( 'past' ) == 'show')
						$defaultNamespace->show = 1;
					else
						$defaultNamespace->show = null;
				}
				
				if ($defaultNamespace->show) {
					$result .= '<a href="" onClick="hidePast(); return false;"><img src="' . $this->_request->getBaseUrl () . '/images/arrow_down.gif"></a>';
				} else {
					$timeToShow = mktime ( $hours - 1, $minutes - 30, 0, $month, $day, $year );
					$showDay = date ( "j", $timeToShow );
					$showHours = date ( "G", $timeToShow );
					$showMinutes = date ( "i", $timeToShow );
					if ($showDay == $day && $showHours >= 8)
						$hidePast = true;
					$result .= '<a href="" title="laat de hele dag zien" onClick="showPast(); return false;"><img src="' . $this->_request->getBaseUrl () . '/images/arrow_up.gif"></a>';
				}
			}
			$result .= '</th>';
			foreach ( $employeesInLocation as $employee )
				$result .= '<th class="employees">' . $employee->name . '</th>';
			$result .= '</tr>';
			
			$timeTable = array ();
			foreach ( $employeesInLocation as $employee ) {
				$timeTable [$employee->id] = array ();
				for($i = 8; $i <= 22; $i ++) {
					for($j = 0; $j < ($i < 22 ? 60 : 15); $j += 15) {
						if (! $hidePast || ($showHours < $i || $showHours == $i && $showMinutes < $j))
							$timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] = 1;
					}
				}
				
				$employeeAppointments = $appointments->getByEmployeeAndDate ( $employee->id, $date );
				foreach ( $employeeAppointments as $appointment ) {
					$rowspan = round ( $appointment->time_difference / 15 ) + 1;
					$tParts = explode ( ':', substr ( $appointment->time_start, 0, 5 ) );
					for($i = $rowspan; $i >= 1; $i --) {
						$t = mktime ( $tParts [0], $tParts [1] );
						
						if (isset ( $timeTable [$employee->id] [date ( 'H:i', $t )] ['rowspan'] )) {
							$timeTable [$employee->id] [date ( 'H:i', $t )] ['rowspan'] = $i;
							if ($appointment->location_id == $locationId) {
								$timeTable [$employee->id] [date ( 'H:i', $t )] ['appointment'] = $appointment->id;
							} else {
								$timeTable [$employee->id] [date ( 'H:i', $t )] ['appointmentOtherLocation'] = $appointment->location_id;
							}
							$timeTable [$employee->id] [date ( 'H:i', $t )] ['customer'] = $appointment->customer_id;
							$timeTable [$employee->id] [date ( 'H:i', $t )] ['style'] = $appointment->style;
							break;
						}
						
						$tParts [1] += 15;
					}
				}
				
				$rowspan = 1;
				foreach ( $timeTable [$employee->id] as $key => $value ) {
					if ($rowspan > 1) {
						$timeTable [$employee->id] [$key] ['rowspan'] = 0;
						$rowspan --;
					}
					
					if ($timeTable [$employee->id] [$key] ['rowspan'] > 1)
						$rowspan = $timeTable [$employee->id] [$key] ['rowspan'];
				}
			}
			
			$customersCache = array ();
			
			for($i = 8; $i <= 22; $i ++) {
				for($j = 0; $j < ($i < 22 ? 60 : 15); $j += 15) {
					if (mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] ) < mktime ( 0, 0, 0, date ( "m", $current ), date ( "d", $current ), date ( "Y", $current ) ))
						$className = "time past";
					elseif (mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] ) > mktime ( 0, 0, 0, date ( "m", $current ), date ( "d", $current ), date ( "Y", $current ) ))
						$className = "time";
					elseif ($hours > $i || $hours == $i && $minutes >= $j + 15)
						$className = "time past";
					elseif ($hours == $i && $minutes >= $j && $minutes < $j + 15)
						$className = "time now";
					else
						$className = "time";
					
					if (isset ( $timeTable [$employee->id] [$this->printTime ( $i, $j )] )) {
						$result .= '<tr><th class="' . $className . '"><div class="timediv">' . $this->printTime ( $i, $j ) . '</div></th>';
						foreach ( $employeesInLocation as $employee ) {
							if ($timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] > 0) {
								if (isset ( $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['customer'] )) {
									if (! isset ( $customersCache [$timeTable [$employee->id] [$this->printTime ( $i, $j )] ['customer']] )) {
										$customersCache [$timeTable [$employee->id] [$this->printTime ( $i, $j )] ['customer']] = $users->getById ( $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['customer'] );
									}
									$customer = $customersCache [$timeTable [$employee->id] [$this->printTime ( $i, $j )] ['customer']];
									
									if (isset ( $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['appointment'] )) {
										$result .= '<td class="appointment' . ($timeTable [$employee->id] [$this->printTime ( $i, $j )] ['style'] ? $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['style'] : '') . '" onClick="showEditAppointment(' . $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['appointment'] . '); return false;" ' . ($timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] > 1 ? 'rowspan="' . $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] . '"' : '') . '>' . ($customer instanceof Model_Table_Row_User ? $customer->getName () : '') . '</td>';
									} else {
										$result .= '<td class="appointment_other' . ($timeTable [$employee->id] [$this->printTime ( $i, $j )] ['style'] ? $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['style'] : '') . '" onClick="appointmentOtherClick(' . $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['appointmentOtherLocation'] . ')" ' . ($timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] > 1 ? 'rowspan="' . $timeTable [$employee->id] [$this->printTime ( $i, $j )] ['rowspan'] . '"' : '') . '>' . ($customer instanceof Model_Table_Row_User ? $customer->getName () : '') . '</td>';
									}
								} else {
									if ($className == 'time past') {
										$result .= '<td class="cells"></td>';
									} else {
										$result .= '<td class="cells" onMouseDown="selectStart(this, ' . $employee->id . ', ' . $i . ', ' . $j . '); return false;" onMouseOver="selectContinue(this, ' . $employee->id . ', ' . $i . ', ' . $j . '); return false;" onMouseUp="selectEnd(this, ' . $employee->id . ', ' . $i . ', ' . $j . '); return false;" onDrag="return false;"></td>';
									}
								}
							}
						}
						$result .= '</tr>';
					}
				}
			}
			
			echo $result;
		}
		
		exit ();
	}
	
	/**
	 * Set customer notes
	 *
	 */
	public function setnotesAction() {
		$users = new Model_Table_Users ( );
		$customer = $users->getById ( ( int ) $this->_request->getPost ( 'id' ) );
		if ($customer instanceof Model_Table_Row_User) {
			$filter1 = new Zend_Filter_StringTrim ( );
			$filter2 = new Zend_Filter_StripTags ( );
			$notes = $filter1->filter ( $filter2->filter ( $this->_request->getPost ( 'notes' ) ) );
			$customer->notes = $notes;
			$customer->save ();
		}
		
		echo '';
		exit ();
	}
	
	/**
	 * Add product to order in accordance with defined location
	 *
	 * @return int
	 */
	public function addproducttoorderAction() {
		$responce = '';
		
		$locations = new Model_Table_Users ( );
		$products = new Model_Table_Products ( );
		$orders = new Model_Table_Orders ( );
		$orderlines = new Model_Table_Orderlines ( );
		$location = $locations->getById ( ( int ) $this->_request->getPost ( 'location' ) );
		$product = $products->getById ( ( int ) $this->_request->getPost ( 'id' ) );
		if ($location instanceof Model_Table_Row_User && $product instanceof Model_Table_Row_Product) {
			$order = $orders->getCreated ( $location->userid, $product->supplier_id )->current ();
			if (! $order instanceof Model_Table_Row_Order) {
				$order = $orders->createRow ();
				$order->location_id = $location->userid;
				$order->supplier_id = $product->supplier_id;
				$order->save ();
			}
			
			$orderline = $orderlines->createRow ();
			$orderline->order_id = $order->id;
			$orderline->product_id = $product->id;
			$orderline->number = ( float ) $this->_request->getPost ( 'number' );
			$orderline->save ();
			
			$responce = $orderline->id;
		}
		
		echo $responce;
		exit ();
	}
	
	/**
	 * Delete line from order
	 *
	 */
	public function deletelinefromorderAction() {
		$orderlines = new Model_Table_Orderlines ( );
		$orderline = $orderlines->getById ( ( int ) $this->_request->getPost ( 'id' ) );
		if ($orderline instanceof Model_Table_Row_Orderline)
			$orderline->delete ();
		
		echo '';
		exit ();
	}
	
	/**
	 * Edit defined invoice line (number only)
	 *
	 */
	public function editorderlineAction() {
		$orderlines = new Model_Table_Orderlines ( );
		$orderline = $orderlines->getById ( ( int ) $this->_request->getPost ( 'id' ) );
		if ($orderline instanceof Model_Table_Row_Orderline) {
			$filter = new Controller_Filter_PrepareFloat ( );
			$orderline->number = ( float ) $filter->filter ( $this->_request->getPost ( 'number' ) );
			$orderline->save ();
		}
		
		echo '';
		exit ();
	}
	
	/////////////////////////
	// Auxiliary functions //
	/////////////////////////
	

	/**
	 * Return string representation of defined time
	 *
	 * @param int $h
	 * @param int $m
	 * @return string
	 */
	private function printTime($h, $m) {
		$h = ( string ) $h;
		if (strlen ( $h ) < 2)
			$h = '0' . $h;
		
		$m = ( string ) $m;
		if (strlen ( $m ) < 2)
			$m = '0' . $m;
		return '' . $h . ':' . $m;
	}
	
	/**
	 * Perform time value validation
	 * Return formatted time string if success and empty string otherwise
	 *
	 * @param string $theTime
	 * @return string
	 */
	private function checkTime($theTime) {
		$timeParts = explode ( ':', $theTime );
		
		$error = false;
		if (isset ( $timeParts [0] )) {
			if ($timeParts [0] < 8 || $timeParts [0] > 22) {
				$error = true;
			} else {
				if (isset ( $timeParts [1] )) {
					if ($timeParts [1] < 0 || $timeParts [1] > 45 || $timeParts [0] == 22 && $timeParts [1] > 15 || $timeParts [1] % 15) {
						$error = true;
					}
				} else {
					$error = true;
				}
			}
		} else {
			$error = true;
		}
		
		if ($error)
			return '';
		else
			return $this->printTime ( $timeParts [0], $timeParts [1] );
	}
	
	/**
	 * Return array with possible end times for defined start time
	 *
	 * @param string $timeStart
	 * @return array
	 */
	private function getTimeEndArray($timeStart) {
		$result = array ();
		
		$timeParts = explode ( ':', $timeStart );
		$time = mktime ( $timeParts [0], $timeParts [1] );
		for($i = 15; $i <= 90; $i += 15) {
			$time = $time + 900;
			$result [] = date ( "H:i", $time );
			if (date ( "H:i", $time ) == '21:15')
				break;
		}
		
		return $result;
	}

}
