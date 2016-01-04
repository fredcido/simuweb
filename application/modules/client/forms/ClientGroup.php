<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientGroup extends Client_Form_CaseGroup
{
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan_group' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'clientGroup' );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupationtimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Meta Empregu' )
			    ->addMultiOptions( $optOccupations );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}