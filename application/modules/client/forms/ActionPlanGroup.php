<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ActionPlanGroup extends Client_Form_CaseGroup
{
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_type' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_name' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_intervention' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan_group' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'actionPlanGroup' );
	
	$printButton = $this->createElement( 'button', 'print_case' )
			     ->setAttrib( 'type', 'button' )
			     ->setAttrib( 'onClick', 'Client.CaseGroup.printCase();' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn green' )
			     ->setLabel( 'Imprime' );
	
	App_Form_Toolbar::build( $this, self::ID, array( $printButton ) );
	$this->addElements( $elements );
    }
    
    /**
     *
     * @param array $data
     * @return boolean 
     */
    public function isValid( $data )
    {
	$this->populate( $data );
	return true;
    }
}