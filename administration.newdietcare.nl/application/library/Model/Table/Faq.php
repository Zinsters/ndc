<?php

/**
 * Model_Table_Faq
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Faq extends Model_Table_Abstract {
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_name = 'ndc_faq';
	
	/**
	 * Name of the row class
	 *
	 * @var string
	 */
	protected $_rowClass = 'Model_Table_Row_Faq';
	
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
	protected $_referenceMap = array ();
	
	/**
	 * Return list of news by defined request
	 *
	 * @param stdClass $params
	 * @return Zend_Db_Table_Rowset
	 */
	public function getByRequest($params) {
		$select = $this->select ();
		$select->from ( array ('n' => 'ndc_faq' ), array ('id', 'question', 'published' ) );
		
		if ($params->question)
			$select->where ( "question like '%" . $params->question . "%'" );
		if (isset ( $params->published ))
			$select->where ( 'published=' . $params->published );
		
		if (! isset ( $params->order ) || ! in_array ( $params->order, array ('question', 'order', 'published' ) )) {
			$params->order = 'order';
		}
		if (! isset ( $params->direction ) || ! in_array ( $params->direction, array ('ASC', 'DESC' ) )) {
			$params->direction = 'ASC';
		}
		$select->order ( $params->order . ' ' . $params->direction );
		
		if ($params->page && $params->rows)
			$select->limit ( $params->rows, $params->rows * $params->page - $params->rows );
		
		return $this->fetchAll ( $select );
	}

}