<?php

/**
 *
 * @author Frederico Estrela
 */
class Sms_Form_CampaignType extends App_Form_Default
{
    const ID = 174;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_campaign_type' )->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'campaign_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tipu Kampanha' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}