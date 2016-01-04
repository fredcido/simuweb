<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_SearchClient extends Job_Form_MatchManual
{

    const ID = 152;
    
    /**
     * 
     */
    public function init()
    {
	$this->setName( 'searchclient' );
	parent::init();
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters();
	
	$optEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optEnterprise[$row->id_fefpenterprise] = $row->enterprise_name;
	
	$element = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Empreza' )
			    ->addMultiOptions( $optEnterprise )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$this->addElement( $element );
    }
}