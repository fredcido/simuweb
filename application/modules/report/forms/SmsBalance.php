<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_SmsBalance extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	$this->removeElement( 'fk_id_dec' );
	$this->removeElement( 'clear' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'sms/balance-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Total Pulsa Departamentu' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}