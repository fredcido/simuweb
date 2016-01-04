<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_Suku extends App_Form_Default
{

    const ID = 42;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_addsucu' )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperSection =  new Register_Model_Mapper_IsicSection();
	$section = $mapperSection->fetchAll();
	
	$mapperCountry =  new Register_Model_Mapper_AddCountry();
	$countries = $mapperCountry->fetchAll();
	
	$optNations[''] = '';
	foreach ( $countries as $country )
	    $optNations[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasaun' )
			    ->addMultiOptions( $optNations )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Distritu' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Sub-Distritu' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'sucu' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Suku' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
    }

}