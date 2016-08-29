<?php

/**
 * Model_Table_Profiles
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Profiles extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_mijn_ndc';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Profile';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('id' );
	
	/**
	 * Dependent tables
	 *
	 * @var array
	 */
	protected $_dependentTables = array ();
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('User' => array ('columns' => array ('user_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return row by user id
	 * 
	 * @param $id
	 * @return Model_Table_Row_Abstract
	 */
	public function getByUserId($id) {
		$select = $this->select ();
		$select->where ( 'user_id=' . $id );
		return $this->fetchRow ( $select );
	}

}