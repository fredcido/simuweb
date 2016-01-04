<?php

/**
 * 
 * @version $Id: Contract.php 412 2014-10-10 09:55:37Z frederico $
 */
class Report_Form_Contract extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/contract-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Lista Kontratu';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        
        $this->getElement('path')->setValue(self::PATH);
        $this->getElement('title')->setValue(self::TITLE);
        
        $elements = array();
        
        $adapter = App_Model_DbTable_Abstract::getDefaultAdapter();
        $mapper = new Fefop_Model_Mapper_Contract();
        
        $rows = $adapter->fetchAll($mapper->getSelectBeneficiary());
        
        $optUsers = array();
        array_unshift($optUsers, '');
        
        foreach ($rows as $row) {
        	$optUsers[$row['id']] = $row['name'];
        }
        
        $elements[] = $this->createElement('select', 'id_beneficiary')
        	->setDecorators($this->getDefaultElementDecorators())
        	->setAttrib('class', 'm-wrap span12 chosen')
        	->setLabel('Benefisiariu')
        	->addMultiOptions($optUsers);
        
        $this->addElements($elements);
    }
}