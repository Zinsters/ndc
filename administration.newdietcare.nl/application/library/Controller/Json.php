<?php

/**
 * Class Controller_Json
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

abstract class Controller_Json extends Controller_Abstract {
	
	/**
	 * Checks if a curent request is JSON request
	 * 
	 */
	public function preDispatch() {
		parent::preDispatch ();
		
		if ($this->_request->isXmlHttpRequest ())
			$this->getHelper ( 'viewRenderer' )->setNoRender ();
		else
			throw new Exception ( 'Access Denied!' );
	}

}
