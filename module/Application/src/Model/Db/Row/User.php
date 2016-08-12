<?php
namespace Application\Model\Db\Row;

class User extends AbstractRow
{    
	public function getName() {
		if ($this->geslacht || $this->initials || $this->tussenvoegsel || $this->achternaam) {
			return trim(($this->geslacht == 'man' ? 'Dhr.' : ($this->geslacht == 'vrouw' ? 'Mw.' : '')) . ' ' . $this->initials . ' ' . $this->tussenvoegsel . ' ' . $this->achternaam);
		} else {
			return '';
		}
	}
}