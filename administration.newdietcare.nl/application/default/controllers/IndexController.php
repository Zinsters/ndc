<?php

/**
 * Class Default_IndexController
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Default_IndexController extends Controller_Default {
	
	/**
	 * Return current role of the user
	 *
	 */
	public function getRole() {
		return '';
	}
	
	/**
	 * The default controller action
	 *
	 */
	public function indexAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		
		if ($this->view->limited) {
			$this->_redirect ( '/admin/news' );
		} else {
			if (isset ( $this->view->location ) && isset ( $defaultNamespace->currentLocationId ) && ! isset ( $this->view->admin ))
				$this->_redirect ( '/location/' );
			else
				$this->_redirect ( '/admin/' );
		}
	}
	
	/**
	 * Perform login or extra login if identity already exists
	 *
	 */
	public function loginAction() {
		$users = new Model_Table_Users ( );
		
		if ($this->_request->isPost () && $this->_request->getPost ( 'username' ) && $this->_request->getPost ( 'password' )) {
			$defaultNamespace = new Zend_Session_Namespace ( 'default' );

			//Collect the data from the user
			$f = new Zend_Filter_StripTags ( );
			$username = $f->filter ( $this->_request->getPost ( 'username' ) );
			$password = $f->filter ( $this->_request->getPost ( 'password' ) );
			
			if ($username && $password) {
				//Do the authentication
				$users = new Model_Table_Users();
				$user = $users->authenticate ( $username, $password );
				
				if ($user) {
					//Success
					if ($user->active) {
						// Hold our cookies
						if ( ! empty( $defaultNamespace->adminUserId ) ) {
							$previousUser = $users->getById( $defaultNamespace->adminUserId );
						}
						if ( ! empty( $previousUser ) && $previousUser->isLocation() && $user->isAdmin() ) {
							$defaultNamespace->previousAdminUserId = $defaultNamespace->adminUserId;
						} else {
							unset( $defaultNamespace->previousAdminUserId );
						}
						$defaultNamespace->adminUserId = $user->userid;

						$this->_redirect ( '/' );
						
						return;
					} else {
						//Failure: user is inactive
						$this->view->errorMessage = 'De gebruikersnaam/wachtwoord combinatie is ongeldig!';
						$this->view->defaultUsername = $username;
					}
				} else {
					//Failure: username/password is missing
					$this->view->errorMessage = 'De gebruikersnaam/wachtwoord combinatie is ongeldig!';
					$this->view->defaultUsername = $username;
				}
			} else {
				//Failure
				$this->view->errorMessage = 'De gebruikersnaam/wachtwoord combinatie is ongeldig!';
				$this->view->defaultUsername = $username;
			}
		}
		
		$this->_helper->layout->disableLayout ();
	}
	
	/**
	 * Perform logout
	 * Clean up all data from previous user
	 *
	 */
	public function logoutAction() {
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		unset ( $defaultNamespace->adminUserId );
		unset ( $defaultNamespace->previousAdminUserId );
		unset ( $defaultNamespace->currentLocationId );
		unset ( $defaultNamespace->currentAdminId );
		unset ( $defaultNamespace->currentCustomerId );
		unset ( $defaultNamespace->currentEmployeeId );
		
		$this->_redirect ( $this->getLoginUrl () );
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

}