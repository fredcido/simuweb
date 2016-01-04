<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_InternationalOccupation extends App_Form_Default
{

    const ID = 47;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_profocupation' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperGroup =  new Register_Model_Mapper_ProfGroup();
	$groups = $mapperGroup->fetchAll();
	
	$optGroups[''] = '';
	foreach ( $groups as $group )
	    $optGroups[$group['id_profgroup']] = $group['acronym'] . ' - '. $group['group_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profgroup' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Grupu' )
			    ->addMultiOptions( $optGroups )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_profsubgroup' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Sub-Grupu' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_profminigroup' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Mini-Grupu' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'ocupation_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Okupasaun' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 1 )
			    ->setAttrib( 'class', 'm-wrap span4' )
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