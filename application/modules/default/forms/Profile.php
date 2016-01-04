<?php

/**
 *
 * @author Frederico Estrela
 */
class Default_Form_Profile extends App_Form_Default
{

    const ID = 5;
    
    public function init()
    {
	$this->setAttrib( 'class', 'form-horizontal' )->setName( 'profile' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_sysuser' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'input-xlarge focused' )
			    ->setLabel( 'Naran Kompleto' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'login' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'input-xlarge focused' )
			    ->setAttrib( 'readOnly', true )
			    ->setLabel( 'Usuariu Sistema' );
	
	$elements[] = $this->createElement( 'password', 'password' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'input-xlarge focused' )
			    ->setLabel( 'Password' );
	
	$elements[] = $this->createElement( 'password', 'confirm_password' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'input-xlarge focused' )
			    ->setLabel( 'Repete Password' );
	
	
	$button = $this->createElement( 'submit', 'save' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn blue' )
			     ->setLabel( 'Halot' );
	
	$this->addElements( $elements );
	
	$displayGroupDecorator = array(
	    'FormElements',
	    array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'form-actions' ) )
	);
	
	$this->addDisplayGroup( array( $button ), 'toolbar', array( 'decorators' => $displayGroupDecorator ) );
	
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}