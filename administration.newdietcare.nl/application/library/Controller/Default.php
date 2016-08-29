<?php

/**
 * Class Controller_Default
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

abstract class Controller_Default extends Controller_Abstract {
	
	/**
	 * Return current role of the user
	 *
	 */
	abstract public function getRole();
	
	/**
	 * Pre-dispatch hook
	 *
	 */
	public function preDispatch() {
		parent::preDispatch ();

		$employees = new Model_Table_Employees ( );
		
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		$users = new Model_Table_Users ();
		$user = $users->getById( $defaultNamespace->adminUserId );

		// Extra logoff
		if ($this->getRole () == 'location' && $user->isAdmin() && ! empty( $defaultNamespace->previousAdminUserId ) ) {
			$defaultNamespace->adminUserId = $defaultNamespace->previousAdminUserId;
			$user = $users->getById( $defaultNamespace->adminUserId );
		}
		
		//Select locations (if user has been logged in as admin)
		if ( ! empty( $user ) ) {
			if ( $user->isAdmin() && empty( $defaultNamespace->previousAdminUserId ) ) {
				if ($this->_request->isPost () && $this->_request->getPost ( 'locationSet' )) {
					if (( int ) $this->_request->getPost ( 'location' ) > 0)
						$defaultNamespace->currentLocationId = ( int ) $this->_request->getPost ( 'location' );
					else
						unset ( $defaultNamespace->currentLocationId );
				}
			
				$defaultNamespace->currentAdminId = $user->userid;
				$this->view->admin = $user;
				$this->view->locations = $users->getLocations ();
			} elseif ( $user->isAdmin() && ! empty( $defaultNamespace->previousAdminUserId ) ) {
				$defaultNamespace->currentLocationId = $defaultNamespace->previousAdminUserId;
			
				$defaultNamespace->currentAdminId = $user->userid;
				$this->view->admin = $user;
			} else {
				$defaultNamespace->currentLocationId = $user->userid;
			}
		}
		
		//Check current day status if location is selected
		if (isset ( $defaultNamespace->currentLocationId )) {
			$ul = $users->getById ( $defaultNamespace->currentLocationId );
			if ($ul instanceof Model_Table_Row_User && $ul->active == 1 )
				$this->view->location = $ul;
			else
				unset ( $defaultNamespace->currentLocationId );

			$days = new Model_Table_Days ( );
			$day = $days->findDay ( date ( 'Y-m-d' ), $defaultNamespace->currentLocationId );
			if ($day instanceof Model_Table_Row_Day) {
				$this->view->currentDay = $day;
			} else {
				$prevDay = $days->findPreviousDay ( $users->getById ( $defaultNamespace->currentLocationId )->userid );
				if ($prevDay instanceof Model_Table_Row_Day && $prevDay->status == 'opened') {
					$this->view->currentDay = $prevDay;
				}
			}
		}
		
		//Open day if it is not opened yet
		if (! $this->view->currentDay && $this->_request->getControllerName () == 'location' && ! ($this->_request->getActionName () == 'index' || $this->_request->getActionName () == 'openday')) {
			$this->_redirect ( '/location/openday/' );
			return;
		}

		//Set current customer
		if (isset ( $defaultNamespace->currentCustomerId )) {
			$currentCustomer = $users->getById ( $defaultNamespace->currentCustomerId );
			if ( $currentCustomer ) {
				$this->view->currentCustomer = $currentCustomer;
				$this->view->nextAppointment = $this->view->currentCustomer->getNextAppointment ();
			} else {
				unset ( $defaultNamespace->currentCustomerId );
			}
		}
		
		//Select employees
		if (isset ( $this->view->location )) {
			$location = $users->getById ( $this->view->location->userid );
			if ($location) {
				$this->view->employees = $employees->getByLocationId ( $location->userid );
				
				if ($this->_request->isPost () && $this->_request->getPost ( 'employeeSet' )) {
					$currentEmployee = $employees->getById ( ( int ) $this->_request->getPost ( 'employee' ) );
				} else {
					$currentEmployee = $employees->getById ( $defaultNamespace->currentEmployeeId );
				}
				
				if (count ( $this->view->employees ) > 0 && (! isset ( $currentEmployee ) || ! $currentEmployee instanceof Model_Table_Row_Employee || ! count ( $employees->getByLocationId ( $location->userid, $currentEmployee->id ) ) > 0))
					$currentEmployee = $this->view->employees->current ();
				
				if ($currentEmployee instanceof Model_Table_Row_Employee) {
					$defaultNamespace->currentEmployeeId = $currentEmployee->id;
					$this->view->currentEmployee = $currentEmployee;
				}
			}
		}
		
		//Unset POST data if location has been selected
		if ($this->_request->isPost () && $this->_request->getPost ( 'locationSet' )) {
			$_SERVER ['REQUEST_METHOD'] = 'GET';
			unset ( $_POST );
		}
		
		//Unset POST data if employee has been selected
		if ($this->_request->isPost () && $this->_request->getPost ( 'employeeSet' )) {
			$_SERVER ['REQUEST_METHOD'] = 'GET';
			unset ( $_POST );
		}

        if ( ! empty( $user ) && $user->isAdmin() && in_array( $user->email, array( 'marleen@newdietcare.nl' ) ) ) {
            $this->view->limited = true;
        }
	}

}
