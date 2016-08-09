<?php
namespace Application\Model;

class User
{
    public $userid;
    public $username;
    public $group;
    public $voornaam;
    public $initials;
    public $tussenvoegsel;
    public $achternaam;
    public $geboortedatum;
    public $thuisadres;
    public $thuisplaats;
    public $thuispostcode;
    public $email;
    public $telefoon;
    public $mobiel;
    public $reg_date;

    public function exchangeArray(array $data)
    {
        $this->userid = !empty($data['userid']) ? $data['userid'] : null;
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->group = !empty($data['group']) ? $data['group'] : null;
        $this->voornaam = !empty($data['voornaam']) ? $data['voornaam'] : null;
        $this->initials = !empty($data['initials']) ? $data['initials'] : null;
        $this->tussenvoegsel = !empty($data['tussenvoegsel']) ? $data['tussenvoegsel'] : null;
        $this->achternaam = !empty($data['achternaam']) ? $data['achternaam'] : null;
        $this->geslacht = !empty($data['geslacht']) ? $data['geslacht'] : null;
        $this->geboortedatum = !empty($data['geboortedatum']) ? $data['geboortedatum'] : null;
        $this->thuisadres = !empty($data['thuisadres']) ? $data['thuisadres'] : null;
        $this->thuisplaats = !empty($data['thuisplaats']) ? $data['thuisplaats'] : null;
        $this->thuispostcode = !empty($data['thuispostcode']) ? $data['thuispostcode'] : null;
        $this->email = !empty($data['email']) ? $data['email'] : null;
        $this->telefoon = !empty($data['telefoon']) ? $data['telefoon'] : null;
        $this->mobiel = !empty($data['mobiel']) ? $data['mobiel'] : null;
        $this->reg_date = !empty($data['reg_date']) ? $data['reg_date'] : null;
    }
}