<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_OccupationTimor extends App_Form_Default
{

    const ID = 46;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_profocupationtimor' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperOccupation =  new Register_Model_Mapper_ProfOcupation();
	$groups = $mapperOccupation->fetchAll();
	
	$optOccupation[''] = '';
	foreach ( $groups as $group )
	    $optOccupation[$group['id_profocupation']] = $group['acronym'] . ' - ' . $group['ocupation_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Okupasaun Internasional' )
			    ->addMultiOptions( $optOccupation )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'ocupation_name_timor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Okupasaun Timor' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setAttrib( 'readOnly', true )
			    ->setLabel( 'Sigla' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 5 )
			    ->setLabel( 'Deskrisaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
    }

}