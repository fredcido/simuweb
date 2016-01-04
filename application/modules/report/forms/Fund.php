<?php

class Report_Form_Fund extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/fund-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Finansiamentu husi Fundu';
    
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
        
        //Tipo FEFOPFund
        $optTypeFEFOPFund = array();
        
        array_unshift($optTypeFEFOPFund, '');
        
        $optTypeFEFOPFund['D'] = 'Donor';
        $optTypeFEFOPFund['G'] = 'Governo';
        
        $element = $this->createElement('select', 'type_fefopfund')
            ->setLabel('Tipu de Fundu')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optTypeFEFOPFund);
        
        $this->addElement($element);
    }
}
