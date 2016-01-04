<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_PCEContract extends App_Form_Default
{
    const ID = 192;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}