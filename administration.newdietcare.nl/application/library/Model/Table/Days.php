<?php

/**
 * Model_Table_Days
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Days extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_days';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Day';
	
	/**
	 * Primary keys
	 *
	 * @var array
	 */
	protected $_primary = array ('id' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Find day info by defined date
	 *
	 * @param string $date ('YYYY-MM-DD')
	 * @param int $locationUserId
	 * @return Model_Table_Row_Day
	 */
	public function findDay($date, $locationUserId) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
		$select->from ( array ('d' => 'ndc_days' ) );
		$select->join ( array ('l' => 'users_data' ), 'd.location_id=l.userid', array () );
		$select->where ( "date='$date'" );
		$select->where ( "l.userid=$locationUserId" );
		$select->where ( 'status="opened"' );
		return $this->fetchRow ( $select );
	}
	
	/**
	 * Find previous day for defined location
	 *
	 * @param int $locationId
	 * @return Model_Table_Row_Day
	 */
	public function findPreviousDay($locationId) {
		$select = $this->select ();
		$select->where ( 'location_id=' . $locationId );
		$select->order ( 'date DESC' );
		$select->order ( 'id DESC' );
		$result = $this->fetchAll ( $select );
		if (count ( $result ) > 0)
			return $result->current ();
		else
			return false;
	}

}
