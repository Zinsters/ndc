<?php

/**
 * Class Default_CustomerController
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Default_CustomerController extends Controller_Default {

	/**
	 * Return current role of the user
	 *
	 */
	public function getRole() {
		return '';
	}

	/**
	 * The default controller action (search)
	 *
	 */
	public function indexAction() {
	}

	/**
	 * Add a new customer
	 *
	 */
	public function newAction() {
		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			if (! isset ( $userData ['gender'] ))
				$userData ['gender'] = '';
			unset ( $userData ['submitButton'] );

			$validName = new Zend_Validate ( );
			$validName->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_Alpha ( true ) );
			$validGender = new Zend_Validate ( );
			$validGender->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_InArray ( array ('man', 'vrouw', 'bedrijf' ) ) );
			$validEmail = new Zend_Validate ( );
			$validEmail->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_EmailAddress ( ) )->addValidator ( new Controller_Validate_Uniq ( 'Model_Table_Users', 'email' ) );

			$filters = array ('passowrd' => array ('StringTrim', 'StripTags' ), 'first_name' => array ('StringTrim', 'StripTags' ), 'initials' => array ('StringTrim', 'StripTags' ), 'infix' => array ('StringTrim', 'StripTags' ), 'last_name' => array ('StringTrim', 'StripTags' ), 'gender' => array ('StringTrim', 'StripTags' ), 'address' => array ('StringTrim', 'StripTags' ), 'number' => array ('StringTrim', 'StripTags' ), 'zip' => array ('StringTrim', 'StripTags' ), 'city' => array ('StringTrim', 'StripTags' ), 'phone' => array ('StringTrim', 'StripTags' ), 'mobile_phone' => array ('StringTrim', 'StripTags' ), 'email' => array ('StringTrim', 'StripTags' ), 'notes' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('password' => 'NotEmpty', 'first_name' => array ($validName, 'allowEmpty' => true ), 'initials' => array (new Controller_Validate_Initials ( ), 'allowEmpty' => true ), 'infix' => array ('allowEmpty' => true ), 'last_name' => $validName, 'gender' => $validGender, 'address' => array ('allowEmpty' => true ), 'number' => array ('allowEmpty' => true ), 'zip' => array ('allowEmpty' => true ), 'city' => array ('allowEmpty' => true ), 'phone' => array ('allowEmpty' => true ), 'mobile_phone' => array ('allowEmpty' => true ), 'email' => array ($validEmail, 'allowEmpty' => true ), 'notes' => array ('allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;

			if ($input->isValid ()) {
				$users = new Model_Table_Users ( );

				$do =  Array(
					'email' => $userData[ 'email' ],
					'username' => $userData[ 'first_name' ] . ' ' . ( $userData[ 'infix' ] ? $userData[ 'infix' ] . ' ' : null ) . $userData[ 'last_name' ],
					'password' => Hash( 'sha512', sha1( strtolower( $userData[ 'email' ] ) . $userData[ 'password' ] ) ),
					'voornaam' => $userData[ 'first_name' ],
					'initials' => $userData[ 'initials' ],
					'tussenvoegsel' => $userData[ 'infix' ],
					'achternaam' => $userData[ 'last_name' ],
					'geslacht' => $userData[ 'gender' ],
					'thuisadres' => $userData[ 'address' ],
					'thuispostcode' => $userData[  'zip' ],
					'thuisplaats' => $userData[  'city' ],
					'telefoon' => $userData[  'phone' ],
					'mobiel' => $userData[  'mobile_phone' ],
                    'notes' => $userData[  'notes' ],
					'active' => 1,
					'group' => 19,
					'nieuwsbrief' => 1
				);
				$user = $users->createRow ( $do );
				$user->save ();
                
                

				$defaultNamespace = new Zend_Session_Namespace ( 'default' );
				$defaultNamespace->currentCustomerId = $user->userid;

				if ($this->_request->getPost ( 'submitButton' ) == 'Ok + Agenda')
					$this->_redirect ( '/location/' );
				else
					$this->_redirect ( '/customer/view/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();

				$this->view->customer = new stdClass ( );
				foreach ( $userData as $key => $value )
					$this->view->customer->$key = $value;
			}
		} else {
			$this->view->customer = new stdClass ( );
		}
	}

	/**
	 * View customer data
	 *
	 */
	public function viewAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		$users = new Model_Table_Users ( );

		if ($this->_request->getParam ( 'id' )) {
			$customer = $users->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if ($customer instanceof Model_Table_Row_User && $customer->isCustomer()) {
				$defaultNamespace->currentCustomerId = $customer->userid;
				$this->view->currentCustomer = $customer;
				$this->view->nextAppointment = $this->view->currentCustomer->getNextAppointment ();
			} else {
				unset ( $defaultNamespace->currentCustomerId );
				unset ( $this->view->currentCustomer );
			}
		}

		if (! isset ( $defaultNamespace->currentCustomerId ))
			$this->_redirect ( '/customer/' );

		$customer = $users->getById ( $defaultNamespace->currentCustomerId );

		$consults = new Model_Table_Consults ( );
		$this->view->lastConsult = $consults->getByCustomerId ( $customer->userid )->current ();

		$measurements = new Model_Table_Measurements ( );
		$sampleData = $measurements->getByUserId ( $customer->userid, 'date DESC' );

		if (count ( $sampleData ) > 0) {
			$paginator = Zend_Paginator::factory ( $sampleData );
			$paginator->setItemCountPerPage ( 10 );
			$paginator->setPageRange ( 10 );

			$currentPage = ( int ) $this->_request->getParam ( 'page' );
			if ($currentPage > 0 and $currentPage <= $paginator->count ())
				$paginator->setCurrentPageNumber ( $currentPage );
			else
				$paginator->setCurrentPageNumber ( 1 );

			Zend_View_Helper_PaginationControl::setDefaultViewPartial ( 'paginator.phtml' );

			$this->view->p_control = $this->view->paginationControl ( $paginator );
			$this->view->paginator = $paginator;
			$this->view->count = count ( $sampleData );
		} else
			$this->view->count = 0;

		$this->view->xml = $this->measurementsToXml ( $customer->userid );
	}

	/**
	 * Edit contact data
	 *
	 */
	public function contactAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$customer = $users->getById ( $defaultNamespace->currentCustomerId );

			$validName = new Zend_Validate ( );
			$validName->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_Alpha ( true ) );
			$validGender = new Zend_Validate ( );
			$validGender->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_InArray ( array ('man', 'vrouw', 'bedrijf' ) ) );
			$validEmail = new Zend_Validate ( );
			$validEmail->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_EmailAddress ( ) )->addValidator ( new Controller_Validate_Uniq ( 'Model_Table_Users', 'email', $customer->userid ) );

			$filters = array ('voornaam' => array ('StringTrim', 'StripTags' ),
			                  'initials' => array ('StringTrim', 'StripTags' ),
			                  'tussenvoegsel' => array ('StringTrim', 'StripTags' ),
			                  'achternaam' => array ('StringTrim', 'StripTags' ),
			                  'geslacht' => array ('StringTrim', 'StripTags' ),
			                  'thuisadres' => array ('StringTrim', 'StripTags' ),
			                  'thuispostcode' => array ('StringTrim', 'StripTags' ),
			                  'thuisplaats' => array ('StringTrim', 'StripTags' ),
			                  'telefoon' => array ('StringTrim', 'StripTags' ),
			                  'mobiel' => array ('StringTrim', 'StripTags' ),
			                  'email' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('voornaam' => array ($validName, 'allowEmpty' => true ),
			                     'initials' => array (new Controller_Validate_Initials ( ), 'allowEmpty' => true ),
			                     'tussenvoegsel' => array ('allowEmpty' => true ),
			                     'achternaam' => $validName,
			                     'geslacht' => $validGender,
			                     'thuisadres' => array ('allowEmpty' => true ),
			                     'thuispostcode' => array ('allowEmpty' => true ),
			                     'thuisplaats' => array ('allowEmpty' => true ),
			                     'telefoon' => array ('allowEmpty' => true ),
			                     'mobiel' => array ('allowEmpty' => true ),
			                     'email' => array ($validEmail, 'allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value ) {
				$userData [$key] = $input->$key;
                        }

			if ( $input->isValid() ) {
				$customer->setFromArray ( $userData );
				$customer->save();

				if ($this->_request->getPost ( 'submitButton' ) == 'OK + Klant') {
					$this->_redirect ( '/customer/customer/' );
					return;
				} else {
					$this->view->growlMessage = 'Data saved successfully';

					$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
					$this->view->currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
				}
			} else {
				$this->view->messages = $input->getMessages ();

				$this->view->customer = $users->getById( $defaultNamespace->currentCustomerId );
				foreach ( $userData as $key => $value ) {
					if (! empty ( $value )) {
						$this->view->customer->$key = $value;
                                        }
                                }
			}
		} else {
			$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
		}
	}

	/**
	 * Edit general information
	 *
	 */
	public function customerAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );

		$customer = $users->getById ( $defaultNamespace->currentCustomerId );
		if (! $customer instanceof Model_Table_Row_User || ! $customer->isCustomer() ) {
			unset ( $shopNamespace->currentCustomerId );
			unset ( $this->view->currentCustomer );
			$this->_redirect ( '/customer/' );
			return;
		}

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			if (! isset ( $userData ['state'] ))
				$userData ['state'] = 0;
			unset ( $userData ['submitButton'] );

			$validName = new Zend_Validate ( );
			$validName->addValidator ( new Zend_Validate_NotEmpty ( ), true )->addValidator ( new Zend_Validate_Alpha ( true ) );

			$filters = array ( 'password' => array ('StringTrim', 'StripTags' ), 'username' => array ('StringTrim', 'StripTags' ), 'discount_percent' => array (new Controller_Filter_PrepareFloat ( ) ), 'relatives' => array ('StringTrim', 'StripTags' ) );
			$validators = array ( 'password' => array ( 'allowEmpty' => true ), 'username' => 'NotEmpty', 'discount_percent' => array ('Float', new Zend_Validate_Between ( 0, 100 ), 'allowEmpty' => true ), 'relatives' => array ('allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;
			if ( $userData ['password'] ) {
				$userData[ 'password' ] = Hash( 'sha512', sha1( strtolower( $customer->email ) . $userData[ 'password' ] ) );
			} else {
				unset( $userData[ 'password' ] );
			}

			if ($input->isValid ()) {
				$customer->setFromArray ( $userData );
				$customer->save ();

				if ($this->_request->getPost ( 'submitButton' ) == 'OK + Intake') {
					$this->_redirect ( '/customer/intake/' );
					return;
				} else {
					$this->view->growlMessage = 'Data saved successfully';

					$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
					$this->view->currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
				}
			} else {
				$this->view->messages = $input->getMessages ();

				$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );

				foreach ( $userData as $key => $value )
					if (! empty ( $value ))
						$this->view->customer->$key = $value;
			}
		} else {
			$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
		}
	}

	/**
	 * Edit current customer general information
	 *
	 */
	public function intakeAction() {
		$this->view->date = new Zend_Date ( );

		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$customer = $users->getById ( $defaultNamespace->currentCustomerId );

			$filterChain = new Zend_Filter ( );
			$filterChain->addFilter ( new Zend_Filter_StringTrim ( ) )->addFilter ( new Zend_Filter_StripTags ( ) );
			$userData ['work_situation'] = $filterChain->filter ( $userData ['work_situation'] );
			$userData ['home_situation'] = $filterChain->filter ( $userData ['home_situation'] );
			$userData ['excercise'] = $filterChain->filter ( $userData ['excercise'] );
			$userData ['smoking'] = $filterChain->filter ( $userData ['smoking'] );
			$userData ['drinking'] = $filterChain->filter ( $userData ['drinking'] );
			$userData ['intake_date'] = $filterChain->filter ( $userData ['intake_date'] );
			if (isset ( $userData ['employee_id'] ))
				$userData ['employee_id'] = ( int ) $userData ['employee_id'];

			$customer->setFromArray ( $userData );
			$customer->save ();

			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Dieet') {
				$this->_redirect ( '/customer/diet/' );
				return;
			} else {
				$this->view->growlMessage = 'Data saved successfully';

				$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
				$this->view->currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
			}
		} else {
			$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
		}
	}

	/**
	 * Edit current customer diet information
	 *
	 */
	public function dietAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/new/' );
			return;
		}

		$users = new Model_Table_Users ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$customer = $users->getById ( $defaultNamespace->currentCustomerId );

			$filters = array ('geboortedatum' => array (new Controller_Filter_PrepareDate ( ) ), 'length' => array (new Controller_Filter_PrepareFloat ( ) ), 'weight' => array (new Controller_Filter_PrepareFloat ( ) ), 'weight_ideal' => array (new Controller_Filter_PrepareFloat ( ) ), 'why_gained_weight' => array ('StringTrim', 'StripTags' ), 'other_diets' => array ('StringTrim', 'StripTags' ), 'dietgoal' => array ('StringTrim', 'StripTags' ), 'notes' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('geboortedatum' => array ('Date', 'allowEmpty' => true ), 'length' => array ('Float', 'allowEmpty' => true ), 'weight' => array ('Float', 'allowEmpty' => true ), 'weight_ideal' => array ('Float', 'allowEmpty' => true ), 'why_gained_weight' => array ('allowEmpty' => true ), 'other_diets' => array ('allowEmpty' => true ), 'dietgoal' => array ('allowEmpty' => true ), 'notes' => array ('allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;

			if ($input->isValid ()) {
				$customer->setFromArray ( $userData );
				$customer->save ();

				if ($this->_request->getPost ( 'submitButton' ) == 'OK + Medisch') {
					$this->_redirect ( '/customer/medical/' );
					return;
				} else {
					$this->view->growlMessage = 'Data saved successfully';

					$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
					$this->view->currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
				}
			} else {
				$this->view->messages = $input->getMessages ();

				$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
				foreach ( $userData as $key => $value )
					if (! empty ( $value ))
						$this->view->customer->$key = $value;
			}
		} else {
			$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
		}
	}

	/**
	 * Edit current customer medical information
	 *
	 */
	public function medicalAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$customer = $users->getById ( $defaultNamespace->currentCustomerId );

			$filterChain = new Zend_Filter ( );
			$filterChain->addFilter ( new Zend_Filter_StringTrim ( ) )->addFilter ( new Zend_Filter_StripTags ( ) );
			$userData ['medical_situation'] = $filterChain->filter ( $userData ['medical_situation'] );
			$userData ['medications'] = $filterChain->filter ( $userData ['medications'] );
			$userData ['under_treatment_with'] = $filterChain->filter ( $userData ['under_treatment_with'] );
			$userData ['darm'] = $filterChain->filter ( $userData ['darm'] );

			$customer->setFromArray ( $userData );
			$customer->save ();

			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Metingen') {
				$this->_redirect ( '/customer/measurements/' );
				return;
			} else {
				$this->view->growlMessage = 'Data saved successfully';

				$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
				$this->view->currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
			}
		} else {
			$this->view->customer = $users->getById ( $defaultNamespace->currentCustomerId );
		}

		$this->view->headcontent = 'customer/medical/headcontent.phtml';
	}

	/**
	 * Information about customer measurements
	 *
	 */
	public function measurementsAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		if ($this->_request->getPost ( 'submitButton' )) {
			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Consult') {
				$this->_redirect ( '/customer/newconsult/' );
				return;
			}
		}

		$users = new Model_Table_Users ( );
		$customer = $users->getById ( $defaultNamespace->currentCustomerId );
		$measurements = new Model_Table_Measurements ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();

			$filters = array ('date' => array (new Controller_Filter_PrepareDate ( ) ), 'weight' => array (new Controller_Filter_PrepareFloat ( ) ), 'spiermassa' => array (new Controller_Filter_PrepareFloat ( ) ), 'fat' => array (new Controller_Filter_PrepareFloat ( ) ), 'fat_p' => array (new Controller_Filter_PrepareFloat ( ) ), 'damp' => array (new Controller_Filter_PrepareFloat ( ) ), 'bmi' => array (new Controller_Filter_PrepareFloat ( ) ), 'start' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('date' => array ('Date' ), 'weight' => array ('notEmpty', 'Float' ), 'spiermassa' => array ('notEmpty', 'Float' ), 'fat' => array ('notEmpty', 'Float' ), 'fat_p' => array ('notEmpty', 'Float', new Zend_Validate_Between ( 0, 100 ) ), 'damp' => array ('notEmpty', 'Float' ), 'bmi' => array ('notEmpty', 'Float' ), 'start' => array ('Int', 'allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;

			if ($input->isValid ()) {
				$userData ['user_id'] = $customer->userid;
				$measurement = $measurements->createRow ( $userData );
				$measurement->save ();
			} else {
				$this->view->messages = $input->getMessages ();
				$this->view->measurement = new stdClass ( );
				foreach ( $userData as $key => $value )
					if (! empty ( $value ))
						$this->view->measurement->$key = $value;
			}
		} else
			$this->view->measurement = new stdClass ( );

		$sampleData = $measurements->getByUserId ( $customer->userid, 'date DESC' );

		if (count ( $sampleData ) > 0) {
			$paginator = Zend_Paginator::factory ( $sampleData );
			$paginator->setItemCountPerPage ( 10 );
			$paginator->setPageRange ( 10 );

			$currentPage = ( int ) $this->_request->getParam ( 'page' );
			if ($currentPage > 0 and $currentPage <= $paginator->count ())
				$paginator->setCurrentPageNumber ( $currentPage );
			else
				$paginator->setCurrentPageNumber ( 1 );

			Zend_View_Helper_PaginationControl::setDefaultViewPartial ( 'paginator.phtml' );

			$this->view->p_control = $this->view->paginationControl ( $paginator );
			$this->view->paginator = $paginator;
			$this->view->count = count ( $sampleData );
		} else
			$this->view->count = 0;

		if (isset ( $this->view->measurement->weight ) && $this->view->measurement->weight > 0 && $profile instanceof Model_Table_Row_Profile && $profile->start_weight > 0)
			$this->view->weightDifference = $customer->getRealStartWeight () - $this->view->measurement->weight;

		$this->view->xml = $this->measurementsToXml ( $customer->userid );
		$this->view->headcontent = 'customer/measurements/headcontent.phtml';
	}

	/**
	 * Edit selected measurement
	 *
	 */
	public function measurementeditAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		if ($this->_request->getPost ( 'submitButton' )) {
			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Consult')
				$this->_redirect ( '/customer/newconsult/' );
			elseif ($this->_request->getPost ( 'submitButton' ) == 'Nieuwe meting')
				$this->_redirect ( '/customer/measurements/' );
			else
				$this->_redirect ( '/customer/view/' );
			return;
		}

		$users = new Model_Table_Users ( );
		$customer = $users->getById ( $defaultNamespace->currentCustomerId );
		$measurements = new Model_Table_Measurements ( );

		if ($this->_request->getParam ( 'id' )) {
			$select = $measurements->select ();
			$select->where ( 'id=' . ( int ) $this->_request->getParam ( 'id' ) );
			$select->where ( 'user_id=' . $customer->userid );
			$measurement = $measurements->fetchRow ( $select );
		}

		if (! isset ( $measurement ) || ! $measurement instanceof Model_Table_Row_Measurement)
			$this->_redirect ( '/customer/measurements/' );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();

			$filters = array ('date' => array (new Controller_Filter_PrepareDate ( ) ), 'weight' => array (new Controller_Filter_PrepareFloat ( ) ), 'spiermassa' => array (new Controller_Filter_PrepareFloat ( ) ), 'fat' => array (new Controller_Filter_PrepareFloat ( ) ), 'fat_p' => array (new Controller_Filter_PrepareFloat ( ) ), 'damp' => array (new Controller_Filter_PrepareFloat ( ) ), 'bmi' => array (new Controller_Filter_PrepareFloat ( ) ), 'start' => array ('StringTrim', 'StripTags' ) );
			$validators = array ('date' => array ('Date' ), 'weight' => array ('notEmpty', 'Float' ), 'spiermassa' => array ('notEmpty', 'Float' ), 'fat' => array ('notEmpty', 'Float' ), 'fat_p' => array ('notEmpty', 'Float', new Zend_Validate_Between ( 0, 100 ) ), 'damp' => array ('notEmpty', 'Float' ), 'bmi' => array ('notEmpty', 'Float' ), 'start' => array ('Int', 'allowEmpty' => true ) );

			$input = new Zend_Filter_Input ( $filters, $validators, $userData );
			foreach ( $userData as $key => $value )
				$userData [$key] = $input->$key;

			if ($input->isValid ()) {
				$measurement->setFromArray ( $userData );
				$measurement->save ();

				$this->_redirect ( '/customer/view/' );
				return;
			} else {
				$this->view->messages = $input->getMessages ();
				$this->view->measurement = $measurement;
				foreach ( $userData as $key => $value )
					if (! empty ( $value ))
						$this->view->measurement->$key = $value;
			}
		} else
			$this->view->measurement = $measurement;

		$sampleData = $measurements->getByUserId ( $customer->userid, 'date DESC' );

		if (count ( $sampleData ) > 0) {
			$paginator = Zend_Paginator::factory ( $sampleData );
			$paginator->setItemCountPerPage ( 10 );
			$paginator->setPageRange ( 10 );

			$currentPage = ( int ) $this->_request->getParam ( 'page' );
			if ($currentPage > 0 and $currentPage <= $paginator->count ())
				$paginator->setCurrentPageNumber ( $currentPage );
			else
				$paginator->setCurrentPageNumber ( 1 );

			Zend_View_Helper_PaginationControl::setDefaultViewPartial ( 'paginator.phtml' );

			$this->view->p_control = $this->view->paginationControl ( $paginator );
			$this->view->paginator = $paginator;
			$this->view->count = count ( $sampleData );
		} else
			$this->view->count = 0;

		if (isset ( $this->view->measurement->weight ) && $this->view->measurement->weight > 0 && $customer instanceof Model_Table_Row_User && $customer->weight_ideal > 0)
			$this->view->weightDifference = $customer->getRealStartWeight () - $this->view->measurement->weight;

		$this->view->xml = $this->measurementsToXml ( $customer->userid );
		$this->view->headcontent = 'customer/measurements/headcontent.phtml';
	}

	/**
	 * Delete selected measurement
	 *
	 */
	public function measurementdeleteAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );
		$customer = $users->getById ( $defaultNamespace->currentCustomerId );
		$measurements = new Model_Table_Measurements ( );

		if ($this->_request->getPost ( 'id' )) {
			$select = $measurements->select ();
			$select->where ( 'id=' . ( int ) $this->_request->getPost ( 'id' ) );
			$select->where ( 'user_id=' . $customer->userid );
			$measurement = $measurements->fetchRow ( $select );
			if ($measurement instanceof Model_Table_Row_Measurement)
				$measurement->delete ();
		}

		$this->_redirect ( '/customer/measurements/' );
	}

	/**
	 * New consult
	 *
	 */
	public function newconsultAction() {
		$this->view->date = new Zend_Date ( );

		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );
		$products = new Model_Table_Products ( );
		$consults = new Model_Table_Consults ( );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$filterChain = new Zend_Filter ( );
			$filterChain->addFilter ( new Zend_Filter_StringTrim ( ) )->addFilter ( new Zend_Filter_StripTags ( ) );

			$userData ['product_id'] = ( int ) $userData ['product_id'];
			$userData ['report'] = $filterChain->filter ( $userData ['report'] );
			$userData ['plan'] = $filterChain->filter ( $userData ['plan'] );
			$userData ['customer_id'] = $this->view->currentCustomer->userid;
			if (isset ( $userData ['employee_id'] ))
				$userData ['employee_id'] = ( int ) $userData ['employee_id'];
			if (isset ( $userData ['location_id'] ))
				$userData ['location_id'] = ( int ) $userData ['location_id'];

			$consult = $consults->createRow ( $userData );
			$consult->save ();

			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Afspraak') {
				$this->_redirect ( '/location/' );
				return;
			} else {
				$this->view->growlMessage = 'Data saved successfully';

				$this->view->consultTypes = $products->getConsults ();
				$this->view->consults = $consults->getByCustomerId ( $this->view->currentCustomer->userid );
				if (isset ( $defaultNamespace->currentLocationId )) {
					$this->view->currentLocation = $users->getById ( $defaultNamespace->currentLocationId );
				}
			}
		} else {
			$this->view->consultTypes = $products->getConsults ();
			$this->view->consults = $consults->getByCustomerId ( $this->view->currentCustomer->userid );
			if (isset ( $defaultNamespace->currentLocationId )) {
				$this->view->currentLocation = $users->getById ( $defaultNamespace->currentLocationId );
			}
		}

		$this->view->headcontent = 'customer/newconsult/headcontent.phtml';
	}

	/**
	 * View existing consult
	 *
	 */
	public function consultAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		if ($this->_request->getPost ( 'submitButton' ) == 'Nieuw consult') {
			$this->_redirect ( '/customer/newconsult/' );
			return;
		}

		$users = new Model_Table_Users ( );
		$products = new Model_Table_Products ( );
		$consults = new Model_Table_Consults ( );

		if ($this->_request->getParam ( 'id' )) {
			$select = $consults->select ();
			$select->where ( 'id=' . ( int ) $this->_request->getParam ( 'id' ) );
			$select->where ( 'customer_id=' . $this->view->currentCustomer->userid );
			$consult = $consults->fetchRow ( $select );
		}

		if (! isset ( $consult ) || ! $consult instanceof Model_Table_Row_Consult)
			$this->_redirect ( '/customer/newconsult/' );

		if ($this->_request->isPost ()) {
			$userData = $this->_request->getPost ();
			unset ( $userData ['submitButton'] );

			$filterChain = new Zend_Filter ( );
			$filterChain->addFilter ( new Zend_Filter_StringTrim ( ) )->addFilter ( new Zend_Filter_StripTags ( ) );

			$userData ['product_id'] = ( int ) $userData ['product_id'];
			$userData ['report'] = $filterChain->filter ( $userData ['report'] );
			$userData ['plan'] = $filterChain->filter ( $userData ['plan'] );
			$userData ['customer_id'] = $this->view->currentCustomer->userid;
			if (isset ( $userData ['employee_id'] ))
				$userData ['employee_id'] = ( int ) $userData ['employee_id'];
			if (isset ( $userData ['location_id'] ))
				$userData ['location_id'] = ( int ) $userData ['location_id'];

			$consult->setFromArray ( $userData );
			$consult->save ();

			if ($this->_request->getPost ( 'submitButton' ) == 'OK + Afspraak') {
				$this->_redirect ( '/location/' );
				return;
			} else {
				$this->view->growlMessage = 'Data saved successfully';

				$this->view->consult = $consult;
				$this->view->consultTypes = $products->getConsults ();
				$this->view->consults = $consults->getByCustomerId ( $this->view->currentCustomer->userid );
			}
		} else {
			$this->view->consult = $consult;
			$this->view->consultTypes = $products->getConsults ();
			$this->view->consults = $consults->getByCustomerId ( $this->view->currentCustomer->userid );
		}

		$this->view->headcontent = 'customer/consult/headcontent.phtml';
	}

	/**
	 * Delete selected consult
	 *
	 */
	public function consultdeleteAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$users = new Model_Table_Users ( );
		$customer = $users->getById ( $defaultNamespace->currentCustomerId );
		$consults = new Model_Table_Consults ( );

		if ($this->_request->getPost ( 'id' )) {
			$select = $consults->select ();
			$select->where ( 'id=' . ( int ) $this->_request->getPost ( 'id' ) );
			$select->where ( 'customer_id=' . $customer->userid );
			$consult = $consults->fetchRow ( $select );
			if ($consult instanceof Model_Table_Row_Consult)
				$consult->delete ();
		}

		$this->_redirect ( '/customer/newconsult/' );
	}

	/**
	 * Create new/edit existing invoice
	 * Invoice must not be burned
	 *
	 */
	public function invoice2Action() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		if (! $this->_request->getParam ( 'id' ) && ! isset ( $defaultNamespace->currentLocationId )) {
			$this->_redirect ( '/' );
			return;
		}

		//Display warning is day is not opened or already closed
		$days = new Model_Table_Days ( );

		$day = $days->getById ( $this->view->currentDay->id );
		if (! isset ( $this->view->admin )) {
			if (! $day instanceof Model_Table_Row_Day || $day->date != date ( 'Y-m-d' )) {
				$this->_helper->viewRenderer->setNoRender ();
				echo $this->view->render ( 'customer/invoice/notopened.phtml' );
				return;
			} elseif ($day->status == 'closed') {
				$this->_helper->viewRenderer->setNoRender ();
				echo $this->view->render ( 'customer/invoice/closed.phtml' );
				return;
			}
		}

		$invoices = new Model_Table_Invoices ( );
		$locations = new Model_Table_Locations ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->burned && ! isset ( $this->view->admin )) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$invoice = $invoices->getCurrent ( $defaultNamespace->currentCustomerId, $locations->getByUserId ( $defaultNamespace->currentLocationId )->id );

			if (! $invoice instanceof Model_Table_Row_Invoice)
				$invoice = $invoices->createRow ();
		}

		if ($this->_request->isPost ()) {
			$invoicelines = new Model_Table_Invoicelines ( );
			$products = new Model_Table_Products ( );

			$filter = new Controller_Filter_PrepareFloat ( );

			if (! $invoice->id) {
				if (! $day instanceof Model_Table_Row_Day) {
					$this->_helper->viewRenderer->setNoRender ();
					echo $this->view->render ( 'customer/invoice/notopened.phtml' );
					return;
				}
				$invoice->customer_id = $defaultNamespace->currentCustomerId;
				$invoice->location_id = $locations->getByUserId ( $defaultNamespace->currentLocationId )->id;
				$invoice->employee_id = $defaultNamespace->currentEmployeeId;
				$invoice->day_id = $day->id;
			}

			$invoice->total = ( float ) $filter->filter ( $this->_request->getPost ( 'total' ) );
			$invoice->reduction = ( float ) $filter->filter ( $this->_request->getPost ( 'reduction' ) );
			$invoice->save ();

			$lines = $invoice->findDependentRowset ( 'Model_Table_Invoicelines' );
			foreach ( $lines as $line )
				$line->delete ();
			$newLines = $this->_request->getPost ( 'invoicebody' );
			$i = 1;
			foreach ( $newLines as $newLine ) {
				$invoiceline = $invoicelines->createRow ();
				$invoiceline->invoice_id = $invoice->id;
				$invoiceline->product_id = ( int ) $newLine ['product'];
				$invoiceline->position = $i;
				$invoiceline->number = ( float ) $filter->filter ( $newLine ['quantity'] );
				$invoiceline->total_price = ( float ) $filter->filter ( $newLine ['total'] );

				$product = $products->getById ( ( int ) $newLine ['product'] );
				$invoiceline->vat_percent = $product->getVatPercent ();

				$invoiceline->save ();
				$i ++;
			}

			switch ($this->_request->getPost ( 'paymentmethod' )) {
				case 'cash' :
					$this->_redirect ( '/customer/cash/id/' . $invoice->id . '/' );
					return;
					break;
				case 'pin' :
					$this->_redirect ( '/customer/pin/id/' . $invoice->id . '/' );
					return;
					break;
				case 'credit' :
					$this->_redirect ( '/customer/credit/id/' . $invoice->id . '/' );
					return;
					break;
				default :
					break;
			}
		}

		$this->view->headcontent = 'customer/invoice2/headcontent.phtml';
		$this->view->invoice = $invoice;
	}

	/**
	 * New version of the invoice
	 *
	 */
	public function invoiceAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		if (! $this->_request->getParam ( 'id' ) && ! isset ( $defaultNamespace->currentLocationId )) {
			$this->_redirect ( '/' );
			return;
		}

		//Display warning is day is not opened or already closed
		$days = new Model_Table_Days ( );

		$day = $days->getById ( $this->view->currentDay->id );
		if (! isset ( $this->view->admin )) {
			if (! $day instanceof Model_Table_Row_Day || $day->date != date ( 'Y-m-d' )) {
				$this->_helper->viewRenderer->setNoRender ();
				echo $this->view->render ( 'customer/invoice/notopened.phtml' );
				return;
			} elseif ($day->status == 'closed') {
				$this->_helper->viewRenderer->setNoRender ();
				echo $this->view->render ( 'customer/invoice/closed.phtml' );
				return;
			}
		}

		$users = new Model_Table_Users ( );
		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->burned && ! isset ( $this->view->admin )) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$invoice = $invoices->getCurrent ( $defaultNamespace->currentCustomerId, $users->getById ( $defaultNamespace->currentLocationId )->userid );

			if (! $invoice instanceof Model_Table_Row_Invoice)
				$invoice = $invoices->createRow ();
		}

		if ($this->_request->isPost ()) {
			$invoicelines = new Model_Table_Invoicelines ( );
			$products = new Model_Table_Products ( );

			$filter = new Controller_Filter_PrepareFloat ( );

			if (! $invoice->id) {
				if (! $day instanceof Model_Table_Row_Day) {
					$this->_helper->viewRenderer->setNoRender ();
					echo $this->view->render ( 'customer/invoice/notopened.phtml' );
					return;
				}
				$invoice->customer_id = $defaultNamespace->currentCustomerId;
				$invoice->location_id = $users->getById ( $defaultNamespace->currentLocationId )->userid;
				$invoice->employee_id = $defaultNamespace->currentEmployeeId;
				$invoice->day_id = $day->id;
			}

			$invoice->total = ( float ) $filter->filter ( $this->_request->getPost ( 'total' ) );
			$invoice->reduction = ( float ) $filter->filter ( $this->_request->getPost ( 'reduction' ) );
			$invoice->save ();

			$lines = $invoice->findDependentRowset ( 'Model_Table_Invoicelines' );
			foreach ( $lines as $line )
				$line->delete ();
			$newLines = $this->_request->getPost ( 'invoicebody' );
			$i = 1;
			foreach ( $newLines as $newLine ) {
				$invoiceline = $invoicelines->createRow ();
				$invoiceline->invoice_id = $invoice->id;
				$invoiceline->product_id = ( int ) $newLine ['product'];
				$invoiceline->position = $i;
				$invoiceline->number = ( float ) $filter->filter ( $newLine ['quantity'] );
				$invoiceline->total_price = ( float ) $filter->filter ( $newLine ['total'] );

				$product = $products->getById ( ( int ) $newLine ['product'] );
				$invoiceline->vat_percent = $product->getVatPercent ();

				$invoiceline->save ();
				$i ++;
			}

			switch ($this->_request->getPost ( 'paymentmethod' )) {
				case 'cash' :
					$this->_redirect ( '/customer/cash/id/' . $invoice->id . '/' );
					return;
					break;
				case 'pin' :
					$this->_redirect ( '/customer/pin/id/' . $invoice->id . '/' );
					return;
					break;
				case 'credit' :
					$this->_redirect ( '/customer/credit/id/' . $invoice->id . '/' );
					return;
					break;
				default :
					break;
			}
		}

		$this->view->headcontent = 'customer/invoice/headcontent.phtml';
		$this->view->invoice = $invoice;
	}

	/**
	 * Finalize the invoice by cash algorithm
	 *
	 */
	public function cashAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->burned && ! isset ( $this->view->admin )) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

		$exVat = 0;
		$invoicelines = $invoice->getLines ();
		foreach ( $invoicelines as $line ) {
			$koef1 = 100 / (100 + $line->vat_percent);
			$exVat += $line->total_price * $koef1;
		}
		$koef2 = ($invoice->total - $invoice->reduction) / $invoice->total;
		$exVatExReduction = round ( $exVat * $koef2, 2 );

		if ($this->_request->isPost ()) {
			$invoice->status = 'payed';
			$invoice->paymentmethod = 'kas';
			$invoice->invoicenumber = '';
			$invoice->save ();

			$this->_redirect ( '/customer/invoices/id/' . $invoice->id . '/' );
			return;
		}

		$invoiceDetails = new Model_Table_Invoicedetails ( );
		$this->view->invoiceDetails = $invoiceDetails->fetchRow ();
		$this->view->invoice = $invoice;
		$this->view->invoicelines = $invoicelines;
		$this->view->exVat = $exVatExReduction;
	}

	/**
	 * Finalize the invoice by pin algorithm
	 *
	 */
	public function pinAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->burned && ! isset ( $this->view->admin )) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

		$exVat = 0;
		$invoicelines = $invoice->getLines ();
		foreach ( $invoicelines as $line ) {
			$koef1 = 100 / (100 + $line->vat_percent);
			$exVat += $line->total_price * $koef1;
		}
		$koef2 = ($invoice->total - $invoice->reduction) / $invoice->total;
		$exVatExReduction = round ( $exVat * $koef2, 2 );

		if ($this->_request->isPost ()) {
			$invoice->status = 'payed';
			$invoice->paymentmethod = 'pin';
			$invoice->invoicenumber = '';
			$invoice->save ();

			$this->_redirect ( '/customer/invoices/id/' . $invoice->id . '/' );
			return;
		}

		$invoiceDetails = new Model_Table_Invoicedetails ( );
		$this->view->invoiceDetails = $invoiceDetails->fetchRow ();
		$this->view->invoice = $invoice;
		$this->view->invoicelines = $invoicelines;
		$this->view->exVat = $exVatExReduction;
	}

	/**
	 * Finalize the invoice by credit algorithm
	 *
	 */
	public function creditAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->burned && ! isset ( $this->view->admin )) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

		$exVat = 0;
		$invoicelines = $invoice->getLines ();
		foreach ( $invoicelines as $line ) {
			$koef1 = 100 / (100 + $line->vat_percent);
			$exVat += $line->total_price * $koef1;
		}
		$koef2 = ($invoice->total - $invoice->reduction) / $invoice->total;
		$exVatExReduction = round ( $exVat * $koef2, 2 );

		if ($this->_request->isPost ()) {
			if (! $invoice->invoicenumber) {
				$numbers = new Model_Table_Lastinvoicenumber ( );
				$number = $numbers->find ( date ( 'Y' ) )->current ();

				if (! $number instanceof Model_Table_Row_Lastinvoicenumber) {
					$number = $numbers->createRow ();
					$number->year = date ( 'Y' );
					$number->number = 1001;
				} else {
					$number->number ++;
				}

				$number->save ();
				$invoice->invoicenumber = $number->getNumber ();
			}

			$invoice->status = 'final';
			$invoice->paymentmethod = 'bank';
			$invoice->save ();

			$this->_redirect ( '/customer/invoices/id/' . $invoice->id . '/' );
			return;
		}

		$invoiceDetails = new Model_Table_Invoicedetails ( );
		$this->view->invoiceDetails = $invoiceDetails->fetchRow ();
		$this->view->invoice = $invoice;
		$this->view->invoicelines = $invoicelines;
		$this->view->exVat = $exVatExReduction;
	}

	/**
	 * Work with customer invoices
	 *
	 */
	public function invoicesAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		$params = new stdClass ( );
		$params->customerId = $defaultNamespace->currentCustomerId;
		$params->statusesAllowed = array ('payed' );
		$this->view->invoicesPayed = $invoices->getByRequest ( clone $params );
		$params->statusesAllowed = array ('open', 'final' );
		$this->view->invoicesNotPayed = $invoices->getByRequest ( clone $params );

		if ($this->_request->getParam ( 'id' ))
			$this->view->invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
	}

	/**
	 * Delete invoice
	 * Status must be 'open'
	 *
	 */
	public function invoicedelAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || $invoice->status != 'open' || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

		if ($this->_request->isPost ())
			$invoice->delete ();

		$this->_redirect ( '/customer/invoices/' );
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
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || $invoice->status != 'final' || $invoice->paymentmethod != 'bank' || ! isset ( $defaultNamespace->currentAdminId ) || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

		if ($this->_request->isPost ()) {
			$invoice->status = 'payed';
			$invoice->save ();
		}

		$this->_redirect ( '/customer/invoices/' );
	}

	/**
	 * Return the invoice in the pdf format
	 *
	 */
	public function invoicepdfAction() {
		set_time_limit ( 180 );
		require_once ("dompdf_config.inc.php");

		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		if (! isset ( $defaultNamespace->currentCustomerId )) {
			$this->_redirect ( '/customer/' );
			return;
		}

		$invoices = new Model_Table_Invoices ( );
		if ($this->_request->getParam ( 'id' )) {
			$invoice = $invoices->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $invoice instanceof Model_Table_Row_Invoice || ($invoice->status != 'final' && $invoice->status != 'payed') || $invoice->customer_id != $defaultNamespace->currentCustomerId) {
				$this->_redirect ( '/customer/invoices/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/invoices/' );
			return;
		}

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
		$contentData ['currentCustomer'] = $this->view->currentCustomer;
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
	 * Customer can be deleted if he has no invoices only
	 *
	 */
	public function deleteAction() {
		$customers = new Model_Table_Users ( );
		if ($this->_request->getParam ( 'id' )) {
			$customer = $customers->getById ( ( int ) $this->_request->getParam ( 'id' ) );
			if (! $customer instanceof Model_Table_Row_User || count ( $customer->findDependentRowset ( 'Model_Table_Invoices' ) ) || count ( $customer->findDependentRowset ( 'Model_Table_Shoporders' ) )) {
				$this->_redirect ( '/customer/' );
				return;
			}
		} else {
			$this->_redirect ( '/customer/' );
			return;
		}

		if ($this->_request->isPost ()) {
			$defaultNamespace = new Zend_Session_Namespace ( 'default' );
			if ($defaultNamespace->currentCustomerId == $customer->userid)
				unset ( $defaultNamespace->currentCustomerId );

			$customer->delete ();
		}

		$this->_redirect ( '/customer/' );
	}

	public function exportAction() {
		$customers = new Model_Table_Users();

		$prepareDate = new Controller_Filter_PrepareDate();

		set_time_limit ( 180 );

		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();

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

		$select = $customers->select();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('c' => 'users_data' ) );
		$select->joinLeft ( array ('p' => 'ndc_mijn_ndc_points' ), 'c.userid=p.user_id', array ('last_measurement' => 'max(p.date)' ) );
		$select->where( '`group` = ?', 19 );
		$select->where( 'active = ?', 1 );
		//$select->where( 'nieuwsbrief = ?', 1 );
		if ( $this->_request->getPost( 'reg_date_year' ) )
			$select->where( 'DATE_FORMAT(FROM_UNIXTIME(reg_date), \'%Y\') = ?', (int) $this->_request->getPost( 'reg_date_year' ) );
		if ( $this->_request->getPost( 'reg_date_from' ) )
			$select->where( 'TO_DAYS(FROM_UNIXTIME(reg_date)) >= TO_DAYS(?)', $prepareDate->filter( $this->_request->getPost( 'reg_date_from' ) ) );
		if ( $this->_request->getPost( 'reg_date_to' ) )
			$select->where( 'TO_DAYS(FROM_UNIXTIME(reg_date)) <= TO_DAYS(?)', $prepareDate->filter( $this->_request->getPost( 'reg_date_to' ) ) );
		if ( $this->_request->getPost( 'no_measurements' ) ) {
			$date = date ( 'Y-m-d' );
			$dateParts = explode ( '-', $date );
			$monthSubtract = date ( 'Y-m-d', mktime ( 0, 0, 0, $dateParts [1] - (int) $this->_request->getPost( 'no_measurements' ), $dateParts [2], $dateParts [0] ) );
			$select->where( 'TO_DAYS(FROM_UNIXTIME(reg_date)) < TO_DAYS(?)', $monthSubtract );
			$select->having( 'TO_DAYS(last_measurement) < TO_DAYS(?)', $monthSubtract );
		}
		if ( $this->_request->getPost( 'with_birthday' ) ) {
			$select->where( 'TO_DAYS( geboortedatum ) > 0' );
		}
		$select->order( 'userid' );
		$select->group( 'userid' );
		//echo $select->__toString();
		//exit;
		$sampleData = $customers->fetchAll ( $select );
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

		rewind( $buffer );
		$content = stream_get_contents( $buffer );
		fclose( $buffer );

		$this->getResponse ()->setHeader ( 'Content-Type', 'text/csv' );
		$this->getResponse ()->setHeader ( 'Content-Length', strlen ( $content ) );
		$this->getResponse ()->setHeader ( 'Content-Disposition', "attachment; filename=\"customers.csv\"" );

		echo $content;
	}

	///////////////////////
	// Private functions //
	///////////////////////


	/**
	 * Generate random customer password
	 *
	 * @return string
	 */
	private function generatePassword() {
		$chars = 'abcdefghijkmnopqrstuvwxyz0123456789';
		$password = NULL;

		for($i = 0; $i < 6; $i ++) {
			$number = rand ( 0, 34 );
			$char = substr ( $chars, $number, 1 );
			$password .= $char;
		}

		return $password;
	}

	/**
	 * Collect all measurements data for current customer in the xml format
	 *
	 * @param $userId
	 * @return string
	 */
	private function measurementsToXml($userId) {
		$measurements = new Model_Table_Measurements ( );
		$points = $measurements->getByUserId ( $userId, 'date' )->toArray ();
		$lastPoint = count ( $points ) - 1;

		$weight = array ();
		$bmi = array ();
		$fatDamp = array ();

		foreach ( $points as $point ) {
			$weight [] = $point ['weight'];
			$bmi [] = $point ['bmi'];
			$fatDamp [] = $point ['fat'];
			$fatDamp [] = $point ['damp'];
		}

		@$lowestWeight = min ( $weight );
		$minWeight = $lowestWeight - fmod ( $lowestWeight, 10 );
		@$lowestBmi = min ( $bmi );
		$minBmi = $lowestBmi - fmod ( $lowestBmi, 10 );
		@$lowestFatDamp = min ( $fatDamp );
		$minFatDamp = $lowestFatDamp - fmod ( $lowestFatDamp, 10 );

		if (count ( $points ) > 1) {
			$dateFormat = Zend_Registry::getInstance ()->get ( 'config' )->application->dateFormat;
			$startDate = strftime ( $dateFormat, strtotime ( $points [0] ['date'] ) );
			$endDate = strftime ( $dateFormat, strtotime ( $points [$lastPoint] ['date'] ) );
			$days = floor ( (strtotime ( $points [$lastPoint] ['date'] ) - strtotime ( $points [0] ['date'] )) / (60 * 60 * 24) );
			$xinterval = ceil ( $days / 10 );
			$weightLines = array (0 => array ('linenumber' => 1, 'key' => 'weight' ) );
			$weight = $this->createXml ( 'kilo', $minWeight, 30, $startDate, $endDate, $xinterval, $weightLines, $points );
			$bmiLines = array (0 => array ('linenumber' => 1, 'key' => 'bmi' ) );
			$bmi = $this->createXml ( 'BMI', $minBmi, 10, $startDate, $endDate, $xinterval, $bmiLines, $points );
			$fatDampLines = array (0 => array ('linenumber' => 1, 'key' => 'damp' ), 1 => array ('linenumber' => 2, 'key' => 'fat' ) );
			$fatDamp = $this->createXml ( 'kilo', $minFatDamp, 10, $startDate, $endDate, $xinterval, $fatDampLines, $points );
			$result = new stdClass();
			$result->weight = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>', '', $weight );
			$result->bmi = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>', '', $bmi );
			$result->fatdamp = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>', '', $fatDamp );
		}

		return $result;
	}

	/**
	 * Return xml document with graphics params
	 *
	 * @param string $tipValue
	 * @param float $yminValue
	 * @param int $yintervalValue
	 * @param string $xminValue
	 * @param string $xmaxValue
	 * @param int $xintervalValue
	 * @param array $graphicLines
	 * @param array $points
	 * @return string
	 */
	private function createXml($tipValue, $yminValue, $yintervalValue, $xminValue, $xmaxValue, $xintervalValue, $graphicLines, $points) {
		$dateFormat = Zend_Registry::getInstance ()->get ( 'config' )->application->dateFormat;

		$dom = new DOMDocument ( '1.0', 'UTF-8' );
		$dom->formatOutput = true;
		$root = $dom->createElement ( 'graphic', '' );
		$dom->appendChild ( $root );
		$dateformat = $dom->createElement ( 'dateformat', 'dd/MM/yyyy' );
		$root->appendChild ( $dateformat );
		$datatipmask = $dom->createElement ( 'datatipmask', 'meetpunt: {0} ' . $tipValue . ' op {1}' );
		$root->appendChild ( $datatipmask );
		$ymin = $dom->createElement ( 'ymin', $yminValue );
		$root->appendChild ( $ymin );
		$yinterval = $dom->createElement ( 'yinterval', $yintervalValue );
		$root->appendChild ( $yinterval );
		$xmin = $dom->createElement ( 'xmin', $xminValue );
		$root->appendChild ( $xmin );
		$xmax = $dom->createElement ( 'xmax', $xmaxValue );
		$root->appendChild ( $xmax );
		$xinterval = $dom->createElement ( 'xinterval', $xintervalValue );
		$root->appendChild ( $xinterval );
		$xdateformat = $dom->createElement ( 'xdateformat', 'dd-MM' );
		$root->appendChild ( $xdateformat );
		$backgroundcolor = $dom->createElement ( 'backgroundcolor', '#FFFFFF' );
		$root->appendChild ( $backgroundcolor );
		$bordercolor = $dom->createElement ( 'bordercolor', '#57565C' );
		$root->appendChild ( $bordercolor );
		$fontsize = $dom->createElement ( 'fontsize', '14' );
		$root->appendChild ( $fontsize );
		$lines = $dom->createElement ( 'lines' );
		$root->appendChild ( $lines );
		$data = $dom->createElement ( 'data' );
		$root->appendChild ( $data );
		$colors = array (1 => '#A2BBDE', 2 => '#999999' );

		foreach ( $graphicLines as $lineData ) {
			$line = $dom->createElement ( 'line', '' );
			$line->setAttribute ( 'key', 'line' . $lineData ['linenumber'] );
			$line->setAttribute ( 'color', $colors [$lineData ['linenumber']] );
			$lines->appendChild ( $line );
		}

		foreach ( $points as $point ) {
			$item = $dom->createElement ( 'item' );
			$data->appendChild ( $item );
			$date = $dom->createElement ( 'date', strftime ( $dateFormat, strtotime ( $point ['date'] ) ) );
			$item->appendChild ( $date );

			foreach ( $graphicLines as $lineData ) {
				$line = $dom->createElement ( 'line' . $lineData ['linenumber'], $point [$lineData ['key']] );
				$item->appendChild ( $line );
			}
		}

		return $dom->saveXML ();
	}

}
