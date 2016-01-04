<?php

/**
 *
 * @author Frederico Estrela
 */
class Sms_Form_Campaign extends App_Form_Default
{
    const ID = 172;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_campaign' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_department' )->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'campaign_title' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Kampanha' )
			    ->setRequired( true );
	
	$mapperConfig = new Admin_Model_Mapper_SmsConfig();
	$config = $mapperConfig->getConfig();
	
	$maxLength = $config->max_length - ( strlen( $config->sms_prefix ) + strlen( $config->sms_sufix ) + 16 );
	
	$elements[] = $this->createElement( 'textarea', 'content' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'maxlength', $maxLength )
			    ->setLabel( 'Mensajem' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_scheduled' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data atu haruka' );
	
	$dbCampaignType = App_Model_DbTable_Factory::get( 'CampaignType' );
	$rows = $dbCampaignType->fetchAll( array(), array( 'campaign_type' ) );
	
	$optCampaignType[''] = '';
	foreach ( $rows as $row )
	    $optCampaignType[$row->id_campaign_type] = $row->campaign_type;
	
	$elements[] = $this->createElement( 'select', 'fk_id_campaign_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Kampanha' )
			    ->addMultiOptions( $optCampaignType )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$mapperGroup = new Sms_Model_Mapper_Group();
	$rows = $mapperGroup->listGroupWithTotals();
	
	$optGroups = array();
	foreach ( $rows as $row )
	    $optGroups[$row['id_sms_group']] = $row['sms_group_name'] . ' (' . $row['total'] . ')';
	
	$elements[] = $this->createElement( 'multiCheckbox', 'group' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->addMultiOptions( $optGroups )
			    ->setAttrib( 'onchange', 'Sms.Campaign.calcTotalSending( this );' )
			    ->setAttrib( 'class', 'group-sending' )
			    ->setRequired( true )
			    ->setSeparator( '' );
	
	$optWaitResponse['0'] = 'Lae';
	$optWaitResponse['1'] = 'Loos';
	
	$elements[] = $this->createElement( 'select', 'wait_response' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Hein Resposta' )
			    ->addMultiOptions( $optWaitResponse )
			    ->setValue( '0' )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}