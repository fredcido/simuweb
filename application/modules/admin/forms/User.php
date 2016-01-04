<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_User extends App_Form_Default
{

    const ID = 95;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_sysuser' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Usuario' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'number_document' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'maxlength', 50 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Numeru Documento' );
			    //->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'login' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Login' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'password', 'password' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Password' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'password', 'confirm_password' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Repete Password' )
			    ->setRequired( true );
	
	$optRadio['1'] = 'Sim';
	$optRadio['0'] = 'Lai';
	
	$elements[] = $this->createElement( 'radio', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Ativu' )
			    ->addMultiOptions( $optRadio )
			    ->setAttrib( 'label_class', 'radio' )
			    ->setSeparator( '' )
			    ->setValue( 1 )
			    ->setRequired( true );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true );
	
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
    
    /**
     *
     * @param array $data
     * @return boolean 
     */
    public function isValid( $data )
    {
	if ( !empty( $data['id_sysuser'] ) ) {
	    
	    $this->getElement( 'password' )->setRequired( false );
	    $this->getElement( 'confirm_password' )->setRequired( false );
	}
	
	return parent::isValid( $data );
    }

}