<?php

class Report_Form_ContractComponent extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/contract-component-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Finansiamentu Kontratu x Komponente';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        
        $this->getElement('path')->setValue(self::PATH);
        $this->getElement('title')->setValue(self::TITLE);
        
        $this->removeElement('fk_id_dec');
        
        //Tipo Despesa
        $rows = App_Model_DbTable_Factory::get('BudgetCategoryType')->fetchAll();
        
        $optBudgetCategoryType = array();
        
        array_unshift($optBudgetCategoryType, '');
        
        foreach ($rows as $row) {
        	$optBudgetCategoryType[$row->id_budget_category_type] = $row->description;
        }
        
        $element = $this->createElement('select', 'id_budget_category_type')
            ->setLabel('Komponente')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optBudgetCategoryType);
        
        $this->addElement($element);
    }
}