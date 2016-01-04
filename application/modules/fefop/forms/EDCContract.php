<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_EDCContract extends Fefop_Form_PERContract
{
    const ID = 200;
    
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
	
	$dbPerArea = App_Model_DbTable_Factory::get( 'PerArea' );
	$areas = $dbPerArea->fetchAll( array( 'fk_id_fefop_modules = ?' => Fefop_Model_Mapper_Module::EDC ), array( 'description' ) );
	
	$optArea[''] = '';
	foreach ( $areas as $area )
	    $optArea[$area['id_per_area']] = $area['description'];
	
	$this->getElement( 'fk_id_per_area' )->addMultiOptions( $optArea );
	
	$elements = array();
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_modules' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setValue( Fefop_Model_Mapper_Module::EDC );
	
	$this->addElements($elements);
    }
}