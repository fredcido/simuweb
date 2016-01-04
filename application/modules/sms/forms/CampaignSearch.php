<?php

/**
 *
 * @author Frederico Estrela
 */
class Sms_Form_CampaignSearch extends App_Form_Default
{

    const ID = 173;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'campaign_title' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Kampanha' );
	
	$dbCampaignType = App_Model_DbTable_Factory::get( 'CampaignType' );
	$rows = $dbCampaignType->fetchAll( array(), array( 'campaign_type' ) );
	
	$optCampaignType[''] = '';
	foreach ( $rows as $row )
	    $optCampaignType[$row->id_campaign_type] = $row->campaign_type;
	
	$elements[] = $this->createElement( 'select', 'fk_id_campaign_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Kampanha' )
			    ->addMultiOptions( $optCampaignType )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
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
	
	$mapperGroup = new Sms_Model_Mapper_Group();
	$rows = $mapperGroup->fetchAll();
	
	$optGroups = array();
	foreach ( $rows as $row )
	    $optGroups[$row['id_sms_group']] = $row['sms_group_name'];
	
	$elements[] = $this->createElement( 'multiCheckbox', 'group' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->addMultiOptions( $optGroups )
			    ->setAttrib( 'class', 'group-sending' )
			    ->setRequired( true )
			    ->setSeparator( '' );
	
	$view = $this->getView();
	$optStatuses = $view->campaign()->getStatuses();
	
	array_unshift( $optStatuses, '' );
	
	$elements[] = $this->createElement( 'select', 'status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addMultiOptions( $optStatuses )
			    ->setLabel( 'Status' )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$this->addElements( $elements );
    }
}