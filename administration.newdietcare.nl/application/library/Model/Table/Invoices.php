<?php

/**
 * Model_Table_Invoices
 *
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Invoices extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_invoices';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Invoice';
	
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
	protected $_dependentTables = array ('Model_Table_Invoicelines' );
	
	/**
	 * Relations
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Customer' => array ('columns' => array ('customer_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Location' => array ('columns' => array ('location_id' ), 'refTableClass' => 'Model_Table_Users', 'refColumns' => array ('userid' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ), 'Employee' => array ('columns' => array ('employee_id' ), 'refTableClass' => 'Model_Table_Employees', 'refColumns' => array ('id' ), 'onDelete' => self::CASCADE, 'onUpdate' => self::CASCADE ) );
	
	/**
	 * Return list of customers by defined request
	 *
	 * @param stdClass $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByRequest($params) {
		$select = $this->select ();
		$select->setIntegrityCheck ( false );
        $select->from ( array ('i' => 'ndc_invoices' ), array ('id' => 'i.id', 'created' => 'i.created', 'date' => 'date_format(i.created,"%d-%m-%Y")', 'time' => 'date_format(i.created,"%H:%i")', 'total' => 'total', 'reduction' => 'reduction', 'totalwithoutreduction' => '(i.total-i.reduction)', 'status' => 'status', 'paymentmethod' => 'paymentmethod', 'burned' => 'burned' ) );
		$select->join ( array ('c' => 'users_data' ), 'c.userid=i.customer_id', array ('customer' => 'CONCAT(IF(c.geslacht = "man","Dhr.",IF(c.geslacht = "vrouw","Mw.","")), " ", IF(ISNULL(c.initials),"",c.initials), " ", IF(ISNULL(c.tussenvoegsel),"",c.tussenvoegsel), " ", c.achternaam)' ) );
		$select->join ( array ('e' => 'ndc_employees' ), 'e.id=i.employee_id', array ('employee' => 'e.name' ) );
		$select->join ( array ('l' => 'users_data' ), 'l.userid=i.location_id', array ('location' => 'l.bedrijfsnaam' ) );
		
		if (isset ( $params->customerId ))
			$select->where ( 'customer_id=' . $params->customerId );
		if (isset ( $params->employeeId ))
			$select->where ( 'i.employee_id=' . $params->employeeId );
		if (isset ( $params->locationId ))
			$select->where ( 'location_id=' . $params->locationId );
		if (isset ( $params->dayId ))
			$select->where ( 'day_id=' . $params->dayId );
		if (isset ( $params->paymentmethod ))
			$select->where ( 'paymentmethod="' . $params->paymentmethod . '"' );
		if (isset ( $params->date ))
			$select->where ( 'TO_DAYS(i.created)=TO_DAYS("' . $params->date . '")' );
		if (isset ( $params->date_start ))
			$select->where ( 'TO_DAYS(i.created)>=TO_DAYS("' . $params->date_start . '")' );
		if (isset ( $params->date_end ))
			$select->where ( 'TO_DAYS(i.created)<=TO_DAYS("' . $params->date_end . '")' );
		if (isset ( $params->price_start ))
			$select->where ( '(i.total-i.reduction)>="' . $params->price_start . '"' );
		if (isset ( $params->price_end ))
			$select->where ( '(i.total-i.reduction)<="' . $params->price_end . '"' );
		
		if (isset ( $params->statusesAllowed )) {
			$statusesAllowed = array ();
			foreach ( $params->statusesAllowed as $status )
				$statusesAllowed [] = "status='$status'";
			$statusString = implode ( ' OR ', $statusesAllowed );
			$select->where ( $statusString );
		}
		
		if (! isset ( $params->order ) || ! in_array ( $params->order, array ('date', 'customer', 'employee', 'location', 'total', 'totalwithoutreduction', 'paymentmethod' => 'paymentmethod' ) )) {
			$params->order = 'date';
		}
		if (! isset ( $params->direction ) || ! in_array ( $params->direction, array ('ASC', 'DESC' ) )) {
			if ($params->order == 'date')
				$params->direction = 'DESC';
			else
				$params->direction = 'ASC';
		}
		if ($params->order == 'date')
			$params->order = 'i.created';
		elseif ($params->order == 'paymentmethod')
			$params->order = 'concat("",i.paymentmethod)';
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ( isset( $params->page) && isset( $params->rows ) )
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		$select->distinct ();
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return sold products of that location_day, payed by cash
	 *
	 * @param int $dayId
	 * @return float
	 */
	public function getDayCashsold($dayId) {
		$select = $this->select ();
		$select->from ( array ('i' => 'ndc_invoices' ), array ('total' => 'SUM(total-reduction)' ) );
		$select->where ( 'day_id=' . $dayId );
		$select->where ( "paymentmethod='kas'" );
		$result = $this->fetchAll ( $select );
		if ($result)
			return $result->current ()->total;
		else
			return 0;
	}
	
	/**
	 * Return sold products of that location_day, payed by pin
	 *
	 * @param int $dayId
	 * @return float
	 */
	public function getDayPinsold($dayId) {
		$select = $this->select ();
		$select->from ( array ('i' => 'ndc_invoices' ), array ('total' => 'SUM(total-reduction)' ) );
		$select->where ( 'day_id=' . $dayId );
		$select->where ( "paymentmethod='pin'" );
		$result = $this->fetchAll ( $select );
		if ($result)
			return $result->current ()->total;
		else
			return 0;
	}
	
	/**
	 * Return opened invoices of the date
	 *
	 * @param int $dayId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getDayInvoicesOpened($dayId) {
		$select = $this->select ();
		$select->where ( 'day_id=' . $dayId );
		$select->where ( "status='open'" );
		return $this->fetchAll ( $select );
	}
	
	/**
	 * Return all invoices of the date
	 *
	 * @param int $dayId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getDayInvoices($dayId) {
		$select = $this->select ();
		$select->where ( 'day_id=' . $dayId );
		return $this->fetchAll ( $select );
	}
	
	public function getCurrent($customerId, $locationId) {
		$select = $this->select ();
		
		$select->where ( 'customer_id=' . $customerId );
		$select->where ( 'location_id=' . $locationId );
		$select->where ( 'burned<1' );
		$select->where ( 'status="open"' );
		
		return $this->fetchRow ( $select );
	}

}
