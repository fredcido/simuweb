<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ActionPlan extends App_Form_Default
{

    const ID = 144;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_action_barrier' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_type' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_name' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_barrier_intervention' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'responsible' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'hidden', 'id_action_plan' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'actionPlan' );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array(), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_counselor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setLabel( 'Konselleru' )
			    ->addMultiOptions( $users );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupationtimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Meta Empregu' )
			    ->addMultiOptions( $optOccupations )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'observation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Observasaun' );
	
	$printButton = $this->createElement( 'button', 'print_case' )
			     ->setAttrib( 'type', 'button' )
			     ->setAttrib( 'disabled', 'disabled' )
			     ->setAttrib( 'onClick', 'Client.Case.printCase();' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn green' )
			     ->setLabel( 'Imprime' );
	
	App_Form_Toolbar::build( $this, self::ID, array( $printButton ) );
	$this->addElements( $elements );
    }
    
    /**
     * 
     */
    public function isValid( $data )
    {
	if ( !empty( $data['id_action_plan'] ) )
	    $this->getElement( 'fk_id_counselor' )->setRequired( false );
	
	return parent::isValid( $data );
    }
}