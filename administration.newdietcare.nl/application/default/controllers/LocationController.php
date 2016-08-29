<?php

/**
 * Class Default_LocationController
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Default_LocationController extends Controller_Default {
	
	/**
	 * Return current role of the user
	 *
	 */
	public function getRole() {
		return 'location';
	}
	
	public function preDispatch() {
		parent::preDispatch ();
		
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $this->view->location )) {
			if (isset ( $defaultNamespace->currentAdminId ) && $this->_request->getActionName () == 'index') {
				if (! $this->_request->getParam ( 'id' )) {
					$locations = new Model_Table_Users ( );
					$locationsSet = $locations->getLocations ();
					if (count ( $locationsSet ) > 0)
						$this->_request->setParam ( 'id', $locationsSet->current ()->userid );
				}
			} else
				$this->_redirect ( '/' );
		}
	}
	
	/**
	 * The default controller action (agenda)
	 *
	 */
	public function indexAction() {
		$users = new Model_Table_Users ( );
		
		if ($this->_request->getParam ( 'id' ))
			$currentLocation = $users->getById ( ( int ) $this->_request->getParam ( 'id' ) );
		if ( isset( $currentLocation ) &&  $currentLocation instanceof Model_Table_Row_User )
			$this->view->currentLocation = $currentLocation;
		else
			$this->view->currentLocation = $users->getById ( $this->view->location->userid );
		
		if ($this->_request->getParam ( 'date' )) {
			$validator = new Zend_Validate_Date ( );
			if ($validator->isValid ( $this->_request->getParam ( 'date' ) )) {
				$date = $this->_request->getParam ( 'date' );
			}
		}
		if ( isset( $date ) )
			$this->view->date = $date;
		else
			$this->view->date = date ( 'Y-m-d' );
		
		$this->view->dateToPrint = new Zend_Date ( );
		$dateParts = explode ( '-', $this->view->date );
		$timestamp = mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] );
		$this->view->monthSubtract = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1] - 1, $dateParts [2], $dateParts [0] ) );
		$this->view->monthAdd = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1] + 1, $dateParts [2], $dateParts [0] ) );
		$this->view->weekSubtract = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1], $dateParts [2] - 7, $dateParts [0] ) );
		$this->view->weekAdd = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1], $dateParts [2] + 7, $dateParts [0] ) );
		$this->view->daySubtract = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1], $dateParts [2] - 1, $dateParts [0] ) );
		$this->view->dayAdd = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1], $dateParts [2] + 1, $dateParts [0] ) );
		$this->view->dateToPrint->set ( $timestamp, Zend_Date::TIMESTAMP );
		$this->view->locationsToShow = $users->getLocations ();
		
		$this->view->headcontent = 'location/index/headcontent.phtml';
	}
	
	/**
	 * Open day
	 *
	 */
	public function opendayAction() {
		$this->view->date = new Zend_Date ( );
		
		$days = new Model_Table_Days ( );
		$day = $days->getById ( $this->view->currentDay->id );
		if ($day instanceof Model_Table_Row_Day) {
			$this->_redirect ( '/location/closeday/' );
			return;
		}
		
		if ($this->_request->getPost ()) {
			$users = new Model_Table_Users ( );
			$location = $users->getById ( $this->view->location->userid );
			$previousDay = $days->findPreviousDay ( $location->userid );
			
			$filter = new Controller_Filter_PrepareFloat ( );
			$cashopen = ( float ) $filter->filter ( $this->_request->getPost ( 'cashopen' ) );
			$cashdifference_open = 0;
			if ($previousDay instanceof Model_Table_Row_Day) {
				$cashdifference_open = $cashopen - $previousDay->cashclose;
				if ($cashdifference_open && ! $this->_request->getPost ( 'confirmed' )) {
					$this->view->result = false;
					$this->view->cashopen = $cashopen;
					$this->view->cashdiference_open = $cashdifference_open;
					return;
				}
			}
			
			$day = $days->createRow ();
			
			$day->location_id = $location->userid;
			$day->date = date ( 'Y-m-d' );
			$day->status = 'opened';
			$day->cashopen = $cashopen;
			$day->cashdifference_open = $cashdifference_open;
			$day->opened_by = $this->view->currentEmployee->id;
			$day->save ();
			$this->view->result = true;
			
			if ($cashdifference_open != 0) {
				$filterPrintPrice = new Controller_Filter_PrintFloat ( 2 );
				
				$html = '<html><body>';
				
				$html .= '
					<h2>Dag openen</h2>
					<table>
						<tr>
							<td style="padding: 0">Locatie:</td>
							<td>' . htmlentities ( $this->view->location->bedrijfsnaam ) . '</td>
						</tr>
						<tr>
							<td>Datum:</td>
							<td>' . htmlentities ( $this->view->date->get ( 'd MMMM yyyy', 'nl_NL' ) ) . '</td>
						</tr>
						<tr>
							<td style="padding: 0">Medewerker:</td>
							<td>' . htmlentities ( $this->view - currentEmployee ? $this->view->currentEmployee->name : '' ) . '</td>
						</tr>
					</table>
					<br />
					<h3>Waarschuwing!</h3>					
					<div>Het opgegeven kas-bedrag is niet correct (&#8364; ' . $filterPrintPrice->filter ( $cashdifference_open ) . ').</div>
				';
				
				$html .= '</body></html>';
				
				$config = Zend_Registry::get ( 'config' );
				
				$mail = new Zend_Mail ( );
				$mail->setBodyHtml ( $html );
				$mail->setFrom ( $config->application->emailSendFrom );
				$mail->addTo ( $config->application->emailAdmin );
				$mail->setSubject ( 'Dag openen' );
				$mail->send ();
			
			}
		}
	}
	
	/**
	 * Close day
	 *
	 */
	public function closedayAction() {
		$this->view->date = new Zend_Date ( );
		
		$days = new Model_Table_Days ( );
		$day = $days->getById ( $this->view->currentDay->id );
		if (! $day) {
			$this->_redirect ( '/location/openday/' );
			return;
		}
		
		if ($day->status == 'closed') {
			$this->_helper->viewRenderer->setNoRender ();
			echo $this->view->render ( 'location/closeday/closed.phtml' );
			return;
		}
		
		// Opened invoices exists
		$users = new Model_Table_Users ( );
		$invoices = new Model_Table_Invoices ( );
		$location = $users->getById ( $this->view->location->userid );
		if (count ( $invoices->getDayInvoicesOpened ( $day->id ) ) > 0) {
			$this->_helper->viewRenderer->setNoRender ();
			echo $this->view->render ( 'location/closeday/openedinvoices.phtml' );
			return;
		}
		
		$dateParts = explode ( '-', $day->date );
		$this->view->date->set ( mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] ), Zend_Date::TIMESTAMP );
		
		if ($this->_request->getPost ()) {
			$filter = new Controller_Filter_PrepareFloat ( );
			$cashclose = ( float ) $filter->filter ( $this->_request->getPost ( 'cashclose' ) );
			$cashextracted = ( float ) $filter->filter ( $this->_request->getPost ( 'cashextracted' ) );
			$pintotal = ( float ) $filter->filter ( $this->_request->getPost ( 'pintotal' ) );
			$cashdifference_close = ($cashextracted + $cashclose) - ($day->cashopen + $invoices->getDayCashsold ( $day->id ));
			$pindifference = $pintotal - $invoices->getDayPinsold ( $day->id );
			if (($cashdifference_close || $pindifference) && ! $this->_request->getPost ( 'confirmed' )) {
				$this->view->result = false;
				$this->view->cashclose = $cashclose;
				$this->view->cashextracted = $cashextracted;
				$this->view->pintotal = $pintotal;
				$this->view->cashdifference_close = $cashdifference_close;
				$this->view->pindifference = $pindifference;
				return;
			}
			
			$day->location_id = $location->userid;
			$day->status = 'closed';
			$day->cashclose = $cashclose;
			$day->cashextracted = $cashextracted;
			$day->pintotal = $pintotal;
			$day->cashdifference_close = $cashdifference_close;
			$day->pindifference = $pindifference;
			$day->closed_by = $this->view->currentEmployee->id;
			$day->save ();
			
			// "Burn" invoices
			$dayInvoices = $invoices->getDayInvoices ( $day->id );
			foreach ( $dayInvoices as $dayInvoice ) {
				$dayInvoice->burned = 1;
				$dayInvoice->save ();
			}
			
			$this->view->result = true;
			
			if ($cashdifference_close || $pindifference) {
				$filterPrintPrice = new Controller_Filter_PrintFloat ( 2 );
				
				$html = '<html><body>';
				
				$html .= '
					<h2>Dag sluiten</h2>
					<table>
						<tr>
							<td style="padding: 0">Locatie:</td>
							<td>' . htmlentities ( $this->view->location->bedrijfsnaam ) . '</td>
						</tr>
						<tr>
							<td>Datum:</td>
							<td>' . htmlentities ( $this->view->date->get ( 'd MMMM yyyy', 'nl_NL' ) ) . '</td>
						</tr>
						<tr>
							<td style="padding: 0">Medewerker:</td>
							<td>' . htmlentities ( $this->view - currentEmployee ? $this->view->currentEmployee->name : '' ) . '</td>
						</tr>
					</table>
					<br />
					<h3>Waarschuwing!</h3>
				';
				if ($pindifference)
					$html .= '
					- Het opgegeven pin-bedrag is niet correct. Er is een verschil van
					&#8364; ' . $filterPrintPrice->filter ( $pindifference ) . '.
					<br />
					';
				if ($cashdifference_close)
					$html .= '	
					- De opgegeven kasbedragen zijn niet correct. Er is een verschil van
					&#8364; ' . $filterPrintPrice->filter ( $cashdifference_close ) . '.
					<br />
					';
				
				$html .= '</body></html>';
				
				$config = Zend_Registry::get ( 'config' );
				
				$mail = new Zend_Mail ( );
				$mail->setBodyHtml ( $html );
				$mail->setFrom ( $config->application->emailSendFrom );
				$mail->addTo ( $config->application->emailAdmin );
				$mail->setSubject ( 'Dag sluiten' );
				$mail->send ();
			}
		}
	}
	
	/**
	 * Page of location invoices of the current day
	 *
	 */
	public function invoicesdayAction() {
		$this->view->date = new Zend_Date ( );
		
		$days = new Model_Table_Days ( );
		$day = $days->getById ( $this->view->currentDay->id );
		
		$dateParts = explode ( '-', $day->date );
		$this->view->date->set ( mktime ( 0, 0, 0, $dateParts [1], $dateParts [2], $dateParts [0] ), Zend_Date::TIMESTAMP );
		
		$invoices = new Model_Table_Invoices ( );
		$params = new stdClass ( );
		$params->dayId = $day->id;
		$params->statusesAllowed = array ('payed' );
		$this->view->invoicesPayed = $invoices->getByRequest ( clone $params );
		$params->statusesAllowed = array ('open', 'final' );
		$this->view->invoicesNotPayed = $invoices->getByRequest ( clone $params );
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
				$this->_redirect ( '/location/invoicesday/' );
				return;
			}
		} else {
			$this->_redirect ( '/location/invoicesday/' );
			return;
		}
		
		if ($this->_request->isPost ())
			$invoice->delete ();
		
		$this->_redirect ( '/location/invoicesday/' );
	}
	
	/**
	 * Change invoice status to 'payed'
	 * - admin permission
	 * - payment_method must be 'bank'
	 * - status must be 'final'
	 *
	 */
	public function invoicepayedAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || $invoice->status != 'final' || $invoice->paymentmethod != 'bank' || ! isset ( $defaultNamespace->currentAdminId )) {
				$this->_redirect ( '/location/invoicesday/' );
				return;
			}
		} else {
			$this->_redirect ( '/location/invoicesday/' );
			return;
		}
		
		if ($this->_request->isPost ()) {
			$invoice->status = 'payed';
			$invoice->save ();
		}
		
		$this->_redirect ( '/location/invoicesday/' );
	}
	
	/**
	 * Return the invoice in the pdf format
	 *
	 */
	public function invoicepdfAction() {
		set_time_limit ( 180 );
		require_once ("dompdf_config.inc.php");
		
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->status != 'final' && $invoice->status != 'payed')) {
				$this->_redirect ( '/location/invoicesday/' );
				return;
			}
		} else {
			$this->_redirect ( '/location/invoicesday/' );
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
					$this->_redirect ( '/customer/invoice/id/' . $invoice->id . '/' );
					return;
				}
			}
		}
		
		$this->_redirect ( '/location/invoicesday/' );
	}
	
	/**
	 * Manipulation with location orders
	 *
	 */
	public function ordersAction() {
		$users = new Model_Table_Users ( );
		$orders = new Model_Table_Orders ( );
		$orderlines = new Model_Table_Orderlines ( );
		$invoices = new Model_Table_Invoices ( );
		$invoicelines = new Model_Table_Invoicelines ( );
		$products = new Model_Table_Products ( );
		$suppliers = new Model_Table_Suppliers ( );
		$location = $users->getById ( $this->view->location->userid );
		
		if ($this->_request->isPost ()) {
			switch ($this->_request->getPost ( 'action' )) {
				case 'new' :
					set_time_limit ( 300 );
					
					$ordersToDelete = $orders->fetchAll ( 'status<>"sent" AND location_id=' . $location->userid );
					foreach ( $ordersToDelete as $orderToDelete ) {
						$orderlines->delete ( 'order_id=' . $orderToDelete->id );
						$orderToDelete->delete ();
					}
					
					//$invoicesToUpdate = $invoices->fetchAll ( '`status`<>"open" AND location_id=' . $location->userid );
					//foreach ( $invoicesToUpdate as $invoiceToUpdate ) {
					$invoicelines->update ( array ('order_status' => 'created' ), 'invoice_id in( select id from ndc_invoices where `status`<>"open" AND location_id=' . $location->userid .' ) AND order_status<>"sent"' );
					//}
					
					$productsToOrders = $products->getProductsToOrders ( $location->userid );
					foreach ( $productsToOrders as $product ) {
						if (! isset ( $newOrder ) || $newOrder->supplier_id != $product->supplier_id) {
							$orderDetails = new Model_Table_Orderdetails ( );
							$orderDetailsRow = $orderDetails->fetchRow ();
							
							$newOrder = $orders->createRow ();
							$newOrder->supplier_id = $product->supplier_id;
							$newOrder->location_id = $location->userid;
							$newOrder->headertext = $orderDetailsRow->header;
							$newOrder->footertext = $orderDetailsRow->footer;
							$newOrder->save ();
						}
						
						$orderline = $orderlines->createRow ();
						$orderline->order_id = $newOrder->id;
						$orderline->product_id = $product->id;
						$orderline->number = $product->number;
						$orderline->save ();
					}
					
					break;
				case 'Order' :
					$result = $orders->getCreated ( $location->userid, ( int ) $this->_request->getPost ( 'supplier_id' ) );
					if (count ( $result ) == 1) {
						$order = $orders->getById ( $result->current ()->id );
						$order->status = 'sent';
						$order->save ();
						
						if (( int ) $this->_request->getPost ( 'supplier_id' ))
							$where = "order_status='created'
							and invoice_id in (select id from ndc_invoices where location_id=" . $location->userid . ")
							and product_id in (select id from webshop_products where supplier_id=" . ( int ) $this->_request->getPost ( 'supplier_id' ) . ")";
						else
							$where = "order_status='created'
							and invoice_id in (select id from ndc_invoices where location_id=" . $location->userid . ")
							and product_id not in (select id from webshop_products where supplier_id>0)";
						$invoicelines->update ( array ('order_status' => 'sent' ), $where );
						
						$html = '<html><body>';
						$html .= '<div>' . htmlentities ( $order->headertext ) . '</div>';
						
						$html .= '
							<table>
								<tr>
									<th>Code</th>
									<th>Product</th>
									<th>Aantal</th>
								</tr>
						';
						
						$number = 0;
						$printFloatFilter = new Controller_Filter_PrintFloat ( );
						foreach ( $orderlines->getByOrderId ( $order->id ) as $orderline ) {
							$html .= '
								<tr>
									<td>' . htmlentities ( $orderline->getProduct ()->productcode ) . '</td>
									<td>' . htmlentities ( $orderline->getProduct ()->name ) . '</td>
									<td>' . $printFloatFilter->filter ( $orderline->number ) . '</td>
								</tr>
							';
							
							$number += $orderline->number;
						}
						
						$html .= '
								<tr>
									<td><b>Totaal</b></td>
									<td></td>
									<td>' . $printFloatFilter->filter ( $number ) . '</td>
								</tr>
							</table>
						';
						
						$html .= '<div>' . htmlentities ( $order->footertext ) . '</div>';
						$html .= '</body></html>';
						
						$config = Zend_Registry::get ( 'config' );
						
						$mail = new Zend_Mail ( );
						$mail->setBodyHtml ( $html );
						$mail->setFrom ( $config->application->emailSendFrom );
						$mail->addTo ( $config->application->emailAdmin );
						
						if ($order->supplier_id) {
							$supplier = $suppliers->getById ( $order->supplier_id );
							if ($supplier instanceof Model_Table_Row_Supplier && $supplier->email) {
								$mail->addTo ( $supplier->email );
							}
						}
						
						$mail->setSubject ( 'New order' );
						$mail->send ();
					}
					break;
				case 'Delete' :
					$result = $orders->getCreated ( $location->userid, ( int ) $this->_request->getPost ( 'supplier_id' ) );
					if (count ( $result ) == 1) {
						$order = $orders->getById ( $result->current ()->id );
						$order->delete ();
					}
					break;
				default :
					break;
			}
		}
		
		$this->view->locationId = $location->userid;
		$this->view->orders = $orders->getCreated ( $location->userid );
		$this->view->orderlines = new Model_Table_Orderlines ( );
		$this->view->headcontent = 'location/orders/headcontent.phtml';
	}

}
