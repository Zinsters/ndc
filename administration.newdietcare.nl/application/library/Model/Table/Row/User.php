<?php

/**
 * Model_Table_Row_User
 * 
 * @author Elena Mukhina <pilotessa@gmail.com>
 */

class Model_Table_Row_User extends Model_Table_Row_Abstract {

	public function isAdmin() {
		if ( in_array( $this->group, array( 1, 15 ) ) )
			return true;
		else
			return false;
	}
	
	public function isLocation() {
		if ( in_array( $this->group, array( 18 ) ) )
			return true;
		else
			return false;
	}

	public function isCustomer() {
		if ( in_array( $this->group, array( 19 ) ) )
			return true;
		else
			return false;
	}

	/**
	 * Return full customer name
	 *
	 * @return string
	 */
	public function getName() {
		if ($this->geslacht || $this->initials || $this->tussenvoegsel || $this->achternaam) {
			return trim(($this->geslacht == 'man' ? 'Dhr.' : ($this->geslacht == 'vrouw' ? 'Mw.' : '')) . ' ' . $this->initials . ' ' . $this->tussenvoegsel . ' ' . $this->achternaam);
		} else {
			return '';
		}
	}

	/**
	 * Return last customer weight
	 *
	 * @return float
	 */
	public function getCurrentWeight() {
		$measurements = new Model_Table_Measurements ( );
		$select = $measurements->select ();
		$select->order ( 'date DESC' );
		$measurementsRowset = $this->findDependentRowset ( 'Model_Table_Measurements', 'User', $select );
		if (count ( $measurementsRowset ) > 0) {
			$currentMeasurement = $measurementsRowset->current ();
			return $currentMeasurement->weight;
		} else
		return '';
	}

	/**
	 * Return first customer weight from measurements table
	 *
	 * @return float
	 */
	public function getRealStartWeight() {
		$measurements = new Model_Table_Measurements ( );
		$select = $measurements->select ();
		$select->order ( 'date ASC' );
		$measurementsRowset = $this->findDependentRowset ( 'Model_Table_Measurements', 'User', $select );
		if (count ( $measurementsRowset ) > 0) {
			$currentMeasurement = $measurementsRowset->current ();
			return $currentMeasurement->weight;
		} else
		return '';
	}

	/**
	 * Return last customer BMI
	 *
	 * @return float
	 */
	public function getCurrentBmi() {
		$measurements = new Model_Table_Measurements ( );
		$select = $measurements->select ();
		$select->order ( 'date DESC' );
		$measurementsRowset = $this->findDependentRowset ( 'Model_Table_Measurements', 'User', $select );
		if (count ( $measurementsRowset ) > 0) {
			$currentMeasurement = $measurementsRowset->current ();
			return $currentMeasurement->bmi;
		} else
		return '';
	}

	/**
	 * Return current customer age
	 *
	 * @return int
	 */
	public function getAge() {
		$db = Zend_Registry::get ( 'db' );

		$sql = "SELECT
			IF (YEAR(`geboortedatum`)=0,
				NULL,
				IF ((MONTH(NOW())>MONTH(`geboortedatum`)) OR ((MONTH(NOW())=MONTH(`geboortedatum`)) AND (DAYOFMONTH(NOW())>=DAYOFMONTH(`geboortedatum`))),
					(YEAR(NOW())-YEAR(`geboortedatum`)),
					(YEAR(NOW())-YEAR(`geboortedatum`)-1)
				)
			)
			as d
			from users_data
			where userid=" . $this->userid;

		$db->setFetchMode ( Zend_Db::FETCH_ASSOC );
		$result = $db->fetchAll ( $sql );

		if (is_array ( $result ))
		return $result [0] ['d']; else
		return '';
	}

	/**
	 * Return start customer weight
	 *
	 * @return string
	 */
	public function getStartWeight() {
		if ($this->weight)
			return $this->weight;
		else
			return '';
	}

	/**
	 * Return target customer weight
	 *
	 * @return string
	 */
	public function getTargetWeight() {
		if ($this->weight_ideal)
			return $this->weight_ideal;
		else
			return '';
	}

	/**
	 * Return target customer weight
	 *
	 * @return string
	 */
	public function getLength() {
		if ($this->length)
			return $this->length;
		else
			return '';
	}

	/**
	 * Find next customer appointment
	 *
	 * @return Model_Table_Row_Appointment
	 */
	public function getNextAppointment() {
		$appointments = new Model_Table_Appointments ( );
		$select = $appointments->select ();
		$select->where ( 'UNIX_TIMESTAMP(\'' . date( 'Y-m-d H:i:s' ) . '\')<UNIX_TIMESTAMP(CONCAT(`date`," ",time_start ))' );
		$select->order ( 'date ASC' );
		$select->order ( 'time_start ASC' );
		return $this->findDependentRowset ( 'Model_Table_Appointments', 'Customer', $select )->current ();
	}

}
