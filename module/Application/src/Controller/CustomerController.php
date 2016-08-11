<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Model\UserTable;
use Application\Model\InvoiceTable;
use Application\Filter\PrepareDate;
use Application\Filter\PrintDate;
use Application\Form\CustomerExportForm;
use Application\Model\CustomerExport;
use Exception;
use stdClass;

class CustomerController extends AbstractActionController
{
    private $userTable;
    private $invoiceTable;
    
    public function __construct( UserTable $userTable, InvoiceTable $invoiceTable )
    {
        $this->userTable = $userTable;
        $this->invoiceTable = $invoiceTable;        
    }

    public function indexAction()
    {
        $form = new CustomerExportForm();
        return new ViewModel( ['form' => $form] );
    }
    
    public function searchAction()
    {
		if ($this->getRequest()->isXmlHttpRequest()) {
			$prepareDate = new PrepareDate;
			$printDate = new PrintDate;
			$params = new stdClass ( );

			if ($this->params()->fromQuery ( 'achternaam' ))
				$params->achternaam = $this->params()->fromQuery ( 'achternaam' );
			if ($this->params()->fromQuery ( 'voornaam' ))
				$params->voornaam = $this->params()->fromQuery ( 'voornaam' );
			if ($this->params()->fromQuery ( 'geboortedatum' ))
				$params->geboortedatum = $prepareDate->filter( $this->params()->fromQuery ( 'geboortedatum' ) );
			if ($this->params()->fromQuery ( 'thuisadres' ))
				$params->thuisadres = $this->params()->fromQuery ( 'thuisadres' );
			if ($this->params()->fromQuery ( 'thuisplaats' ))
				$params->thuisplaats = $this->params()->fromQuery ( 'thuisplaats' );
			if ($this->params()->fromQuery ( 'thuispostcode' ))
				$params->thuispostcode = $this->params()->fromQuery ( 'thuispostcode' );
		
			$order = $this->params()->fromPost ( 'sidx' );
			if (in_array ( $order, array ('achternaam', 'voornaam', 'tussenvoegsel', 'geboortedatum', 'thuisadres', 'thuisplaats', 'thuispostcode', 'email' ) )) {
				$params->order = $order;
			} else {
				$params->order = 'achternaam';
			}
			$direction = $this->params()->fromPost ( 'sord' );
			if (in_array ( strtoupper ( $direction ), array ('ASC', 'DESC' ) )) {
				$params->direction = strtoupper ( $direction );
			} else {
				$params->direction = 'ASC';
			}
		
			$allData = $this->userTable->getByRequest ( clone $params );
		
			$params->page = ( int ) $this->params()->fromPost ( 'page' );
			$params->rows = ( int ) $this->params()->fromPost ( 'rows' );
		
			$sampleData = $this->userTable->getByRequest ( clone $params );
			$response = array();
		
			if (count ( $sampleData ) > 0) {
				$response['page'] = $params->page;
				$response['total'] = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
				$response['records'] = count ( $allData );
				$i = 0;
				foreach ( $sampleData as $row ) {
					$customerInvoices = $this->invoiceTable->getCustomerInvoices( $row->userid );
					if (count ( $customerInvoices ) > 0) {
						$buttonString = '';
					} else {
						$buttonString = '
						<form method="post"
						action="' . $this->url()->fromRoute('customer', ['action' => 'delete']) . '"
						onsubmit="return confirm(' . "'" . 'klant verwijderen?' . "'" . ')"><input type="submit"
						class="delete" value="" title="verwijder" onclick="stopSelect=true;"><input type="hidden"
						name="id" value="' . $row->userid . '"></form>
						';
					}

					$response['rows'] [$i] ['id'] = $row->userid;
					$response['rows'] [$i] ['cell'] = array (htmlentities ( $row->achternaam ), htmlentities ( $row->voornaam ), htmlentities ( $row->tussenvoegsel ), $printDate->filter ( $row->geboortedatum ), htmlentities ( $row->thuisadres ), htmlentities ( $row->thuisplaats ), htmlentities ( $row->thuispostcode ), htmlentities ( $row->email ), $buttonString );
					$i ++;
				}
			}

    		return new JsonModel($response);
		} else {
			throw new Exception ( 'Access Denied!' );
		}
    }
    
	public function exportAction() {
		set_time_limit ( 180 );

		$form = new CustomerExportForm();

		$request = $this->getRequest();

        if (! $request->isPost()) {
            $this->redirect()->toRoute('customer');
        }
        		
        $customerExport = new CustomerExport();
        $form->setInputFilter($customerExport->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            $this->redirect()->toRoute('customer');
        }

        $customerExport->exchangeArray($form->getData());
		
		$buffer = fopen( 'php://temp', 'r+' );

		$data = array(
			'Userid',
			'Username',
			'E-mail',
			'Voornaam',
			'Voorletters',
			'Tussenvoegsel',
			'Achternaam',
			'Geslacht',
			'Geb. datum',
			'Adres',
			'Postcode',
			'Woonplaats',
			'Telefoon',
			'Mobiel',
			'Registratie datum'
		);
		fputcsv( $buffer, $data, ';', '"', '\\' );

		$sampleData = $this->userTable->getForExport ( $customerExport );

		if (count ( $sampleData ) > 0) {
			foreach ( $sampleData as $row ) {
				$data = array(
					$row->userid,
					$row->username,
					$row->email,
					$row->voornaam,
					$row->initials,
					$row->tussenvoegsel,
					$row->achternaam,
					$row->geslacht,
					$row->geboortedatum,
					$row->thuisadres,
					$row->thuispostcode,
					$row->thuisplaats,
					$row->telefoon,
					$row->mobiel,
					( $row->reg_date ? date( 'd-m-Y', $row->reg_date ) : '' )
				);
					
				fputcsv( $buffer, $data, ';', '"', '\\' );
			}
		}
			
		rewind( $buffer );
		$content = stream_get_contents( $buffer );
		fclose( $buffer );

		$response = $this->getResponse();
		$response->getHeaders()
			->addHeaderLine('Content-Type', 'text/csv')
			->addHeaderLine('Content-Disposition', "attachment; filename=\"customers.csv\"")
			->addHeaderLine('Content-Length', strlen($content));

		$response->setContent( $content );
		return $response;
	}    
}
