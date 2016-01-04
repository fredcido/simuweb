<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_District extends App_Form_Default
{

    const ID = 43;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_adddistrict' )->setDecorators( array( 'ViewHelper' ) );
	
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

	$elements[] = $this->createElement( 'text', 'District' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 3 )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Sigla' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}