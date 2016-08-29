<?php

/**
 * Model_Table_Users
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Users extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'users_data';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_User';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('userid' );
	
	/**
	 * Dependent tables
	 *
	 * @var array
	 */
	protected $_dependentTables = array ('Model_Table_Measurements', 'Model_Table_Consults', 'Model_Table_Invoices', 'Model_Table_Appointments', 'Model_Table_Days' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ( );
	
	/**
	 * Return the user by defined username
	 * 
	 * @param string $userName
	 * @return Model_Table_Row_User
	 */
	public function getByUsername($username) {
		return $this->fetchRow ( "username='$username'" );
	}
	
	public function getLocations() {
		return $this->fetchAll( '`group` = 18' );
	}
	
	/**
	 * Return list of customers by defined request
	 *
	 * @param stdClass $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByRequest($params) {
		if (! $params->order)
			$params->order = 'achternaam';
		if (! $params->direction)
			$params->direction = 'ASC';
		
		$select = $this->select ();
		$select->where ( '`group` = 19' );
		
		if (isset($params->achternaam)) {
			$select->where ( "achternaam like '" . $params->achternaam . "%'" );
		}
		if (isset($params->voornaam)) {
			$select->where ( "voornaam like '" . $params->voornaam . "%'" );
		}
		if (isset($params->geboortedatum)) {
			$select->where ( "geboortedatum like '" . $params->geboortedatum . "%'" );
		}
		if (isset($params->thuisadres)) {
			$select->where ( "thuisadres like '" . $params->thuisadres . "%'" );
		}
		if (isset($params->thuisplaats)) {
			$select->where ( "thuisplaats like '" . $params->thuisplaats . "%'" );
		}
		if (isset($params->thuispostcode)) {
			$select->where ( "thuispostcode like '" . $params->thuispostcode . "%'" );
		}
		if (isset($params->email)) {
			$select->where ( "email like '" . $params->email . "%'" );
		}
		
		$select->order ( $params->order . ' ' . $params->direction );
		
		if (isset($params->page) && isset($params->rows))
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		$select->distinct ();
		return $this->fetchAll ( $select );
	}

	/**
	 * Returns a logged in user
	 *
	 * @param string $username
	 * @param string $password
	 * @return Model_Table_Row_User
	 */
	public function authenticate($username, $password) {
        if( filter_var( $username, FILTER_VALIDATE_EMAIL ) ) {
            $pass = hash( 'sha512', ( sha1( strtolower($username) . $password ) ) );
        } else {
            $weNeedEmail = $this->getByUsername( $username );
            $pass = hash( 'sha512', ( sha1( strtolower( $weNeedEmail->email ) . $password ) ) );
        }

		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('u' => 'users_data' ), array( 'userid' => 'u.userid', 'email' => 'u.email', 'active' => 'u.active' ) );
		$select->join ( array ('g' => 'users_groups' ), 'g.groupid=u.group', array () );

		$select->where ( "username = ? or email = ?", $username );
		$select->where ( "password = ?", $pass );
		$select->where ( "g.access_acp = 1" );

		return $this->fetchRow ( $select );
	}
}
