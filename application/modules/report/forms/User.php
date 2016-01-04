<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_User extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setValue( 'admin/user-report' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setValue( 'Relatoriu: Uzuariu' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}