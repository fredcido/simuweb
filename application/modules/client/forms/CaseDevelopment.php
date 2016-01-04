<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_CaseDevelopment extends Client_Form_ActionPlan
{

    const ID = 59;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_action_barrier' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_type' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_name' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_intervention' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'date_finish' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'status' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'caseDevelopment' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}