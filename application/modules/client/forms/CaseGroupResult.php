<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_CaseGroupResult extends Client_Form_CaseGroup
{
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan_group' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'caseGroupResult' );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_intervention' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_name' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_type' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'status' )->setIsArray( true );
	
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
    
    /**
     * 
     */
    public function isValid( $data )
    {
	$this->populate( $data );
	return true;
    }
}