<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_SmsSending extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'fk_id_dec' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'sms/sending-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Enviu sira' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	
	$mapperDepartment = new Admin_Model_Mapper_Department();
	$rows = $mapperDepartment->fetchAll();
	
	$optDepartment[''] = '';
	foreach ( $rows as $row )
	    $optDepartment[$row->id_department] = $row->name;
	
	$elements[] = $this->createElement( 'select', 'fk_id_department' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Departamentu' )
			    ->addMultiOptions( $optDepartment );
	
	$mapperCampaign = new Sms_Model_Mapper_Campaign();
	$rows = $mapperCampaign->listByFilters();
	
	$optCampaign[''] = '';
	foreach ( $rows as $row )
	    $optCampaign[$row->id_campaign] = $row->campaign_title;
	
	$elements[] = $this->createElement( 'select', 'fk_id_campaign' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kampanha' )
			    ->addMultiOptions( $optCampaign );
	
	$optStatus[''] = '';
	$optStatus['E'] = 'Sala';
	$optStatus['S'] = 'Loos';
	
	$elements[] = $this->createElement( 'select', 'status_sending' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span6' )
			    ->setLabel( 'Status enviu' )
			    ->addMultiOptions( $optStatus );
	
	$this->addElements( $elements );
    }
}