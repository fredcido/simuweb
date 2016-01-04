<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_SmsConfig extends App_Form_Default
{
    const ID = 169;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	

	$elements[] = $this->createElement( 'text', 'error_attempts' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric' )
			    ->setLabel( 'Tentativa Hira' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'sms_unit_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Kustu Unitariu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'gateway_url' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 300 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Gateway URL' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'sms_unit_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Kustu Unitariu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'max_length' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric' )
			    ->setLabel( 'Max length' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'sent_by_second' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric' )
			    ->setLabel( 'Haruka ba segundu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'sms_prefix' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addFilter( 'StringTrim' )
			    ->setLabel( 'Prefixu' );
	
	$elements[] = $this->createElement( 'text', 'sms_sufix' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Sufixo' );
	
	$optSimCards[''] = '';
	for ( $i = 1; $i <= 9; $i++ )
	    $optSimCards[$i] = $i;
	
		$elements[] = $this->createElement( 'select', 'sim_cards' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optSimCards )
			    ->setLabel( 'SimCard Hira' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}