<?php

/**
 * 
 * @version $Id: BalanceSource.php 435 2014-10-16 07:09:02Z frederico $
 */
class Report_Form_BalanceSource extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/balance-source-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Previsaun Osan husi Fonte';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        
        $this->getElement('path')->setValue(self::PATH);
        $this->getElement('title')->setValue(self::TITLE);
        
        $this->removeElement('date_start');
        $this->removeElement('date_finish');
        $this->removeElement('id_adddistrict');
        $this->removeElement('fk_id_dec');
        
        $elements = array();
        
        //Tipo FEFOPFund
        $optTypeFEFOPFund = array();
        
        array_unshift($optTypeFEFOPFund, '');
        
        $optTypeFEFOPFund['D'] = 'Donor';
        $optTypeFEFOPFund['G'] = 'Governo';
        
        $elements[] = $this->createElement('select', 'type_fefopfund')
            ->setLabel('Tipu de Fundu')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optTypeFEFOPFund);
        
        //Tipo Despesa
        $rows = App_Model_DbTable_Factory::get('BudgetCategoryType')->fetchAll();
        
        $optBudgetCategoryType = array();
        
        array_unshift($optBudgetCategoryType, '');
        
        foreach ($rows as $row) {
        	$optBudgetCategoryType[$row->id_budget_category_type] = $row->description;
        }
        
        $elements[] = $this->createElement('select', 'id_budget_category_type')
            ->setLabel('Komponente')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optBudgetCategoryType);
        
        //Ano
        $year = date('Y');
        $interval = 10;
        
        $keys = $values = range(($year - $interval), ($year + $interval));
        
        $optYear = array('' => '');
        $optYear += array_combine($keys, $values);
        
        $elements[] = $this->createElement('select', 'year_start')
            ->setLabel('Tinan')
            ->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optYear);
        
        $elements[] = $this->createElement('select', 'year_finish')
            ->setLabel('To\'o')
            ->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optYear);
        
        $this->addElements($elements);
    }
}