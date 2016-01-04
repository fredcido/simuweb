<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_JobTrainingTraineeSearch extends StudentClass_Form_JobTrainingSearch
{

    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement( 'text', 'name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kliente' );
	
	$optGender[''] = '';
	$optGender['FETO'] = 'FETO';
	$optGender['MANE'] = 'MANE';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Seksu' )
			    ->addMultiOptions( $optGender );
	
	$this->addElements( $elements );
    }
}