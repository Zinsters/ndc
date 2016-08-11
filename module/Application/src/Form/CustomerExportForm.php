<?php
namespace Application\Form;

use Zend\Form\Form;

class CustomerExportForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('application');

        $years = array( '0' => '' );
		for( $i = date( 'Y' ); $i >= 2009; $i-- ) {
			$years[ $i ] = $i;
		}

        $this->add([
            'name' => 'reg_date_year',
            'type' => 'select',
            'options' => array(
            	'value_options' => $years,
            ),
        ]);
        $this->add([
            'name' => 'reg_date_from',
            'type' => 'text',
            'attributes' => [
                'id' => 'reg_date_from',
            ],
        ]);
        $this->add([
            'name' => 'reg_date_to',
            'type' => 'text',
            'attributes' => [
                'id' => 'reg_date_to',
            ],
        ]);
        $this->add([
            'name' => 'no_measurements',
            'type' => 'select',
            'options' => array(
            	'value_options' => array(
            		'0' => '',
            		'2' => '2 months',
            		'4' => '4 months',
            		'6' => '6 months',
            		'8' => '8 months',
            		'10' => '10 months',
            		'12' => '12 months',
            	),
            ),
        ]);
        $this->add([
            'name' => 'with_birthday',
            'type' => 'checkbox',
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Export',
            ],
        ]);
    }
}