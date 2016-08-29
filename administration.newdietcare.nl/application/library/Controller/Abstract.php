<?php

/**
 * Class Controller_Abstract
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

abstract class Controller_Abstract extends Zend_Controller_Action {
	
	/**
	 * Pre-dispatch hook
	 *
	 */
	public function preDispatch() {
		parent::preDispatch ();

		$db = Zend_Registry::get ( 'db' );
		$db->query ( 'SET lc_time_names = "nl_NL"' );
		
		$defaultNamespace = new Zend_Session_Namespace ( 'default' );
		
		//Set url params
		$this->view->baseUrl = $this->_request->getBaseUrl ();
		$this->view->controllerName = $this->_request->getControllerName ();
		$this->view->actionName = $this->_request->getActionName ();
		
		//Read information about groups and permissions from database
		if ( ! isset( $defaultNamespace->adminUserId ) ) {
			if (($this->_request->getControllerName () != 'index' || $this->_request->getActionName () != 'login')) {
				$this->_redirect ( $this->getLoginUrl () );
				return;
			}
		}
		$users = new Model_Table_Users ( );
		$user = $users->getById( $defaultNamespace->adminUserId );
		if ( ! $user || $user->active <> 1 || ! ( $user->isAdmin() || $user->isLocation() ) ) {
			if (($this->_request->getControllerName () != 'index' || $this->_request->getActionName () != 'login')) {
				$this->_redirect ( $this->getLoginUrl () );
				return;
			}
		}
		
		$this->view->printFloat = new Controller_Filter_PrintFloat ( );
		$this->view->printPrice = new Controller_Filter_PrintFloat ( 2 );
		$this->view->printDate = new Controller_Filter_PrintDate ( );
		$this->view->printFullDate = new Controller_Filter_PrintDate ( 'd MMMM yyyy' );
	}
	
	protected function getLoginUrl() {
		return 'http://' . $_SERVER ['SERVER_NAME'] . Zend_Registry::getInstance ()->get ( 'config' )->application->baseUrl . '/index/login/';
	}
}
