<?php
// module/Application/src/Model/CustomerExport.php:
namespace Application\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Application\Filter\PrepareDate;

class CustomerExport implements InputFilterAwareInterface
{
    public $reg_date_year;
    public $reg_date_from;
    public $reg_date_to;
    public $no_measurements;
    public $with_birthday;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->reg_date_year     = !empty($data['reg_date_year']) ? $data['reg_date_year'] : null;
        $this->reg_date_from = !empty($data['reg_date_from']) ? $data['reg_date_from'] : null;
        $this->reg_date_to  = !empty($data['reg_date_to']) ? $data['reg_date_to'] : null;
        $this->no_measurements  = !empty($data['no_measurements']) ? $data['no_measurements'] : null;
        $this->with_birthday  = !empty($data['with_birthday']) ? $data['with_birthday'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'reg_date_year'     => $this->reg_date_year,
            'reg_date_from' => $this->reg_date_from,
            'reg_date_to'  => $this->reg_date_to,
            'no_measurements'  => $this->no_measurements,
            'with_birthday'  => $this->with_birthday,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'reg_date_year',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'reg_date_from',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => PrepareDate::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'reg_date_to',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => PrepareDate::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'no_measurements',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        
        $inputFilter->add([
            'name' => 'with_birthday',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
}