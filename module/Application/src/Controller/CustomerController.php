<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Model\UserTable;
use Application\Model\InvoiceTable;
use Application\Filter\PrepareDate;
use Application\Filter\PrintDate;
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
        return new ViewModel();
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
			$responce = array();
		
			if (count ( $sampleData ) > 0) {
				$responce['page'] = $params->page;
				$responce['total'] = $params->rows ? ceil ( count ( $allData ) / $params->rows ) : 0;
				$responce['records'] = count ( $allData );
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

					$responce['rows'] [$i] ['id'] = $row->userid;
					$responce['rows'] [$i] ['cell'] = array (htmlentities ( $row->achternaam ), htmlentities ( $row->voornaam ), htmlentities ( $row->tussenvoegsel ), $printDate->filter ( $row->geboortedatum ), htmlentities ( $row->thuisadres ), htmlentities ( $row->thuisplaats ), htmlentities ( $row->thuispostcode ), htmlentities ( $row->email ), $buttonString );
					$i ++;
				}
			}

    		return new JsonModel($responce);
		} else {
			throw new Exception ( 'Access Denied!' );
		}
    }    
}
