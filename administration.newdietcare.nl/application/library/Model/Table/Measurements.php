<?php

/**
 * Model_Table_Measurements
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Measurements extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_mijn_ndc_points';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Measurement';
	
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
	protected $_referenceMap = array ('User' => array ('columns' => array ('user_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return rows by user id
	 * 
	 * @param int $id
	 * @param string $order
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByUserId($id, $order) {
		$select = $this->select ();
		$select->where ( 'user_id=' . $id );
		if ($order)
			$select->order ( $order );
		return $this->fetchAll ( $select );
	}

}
