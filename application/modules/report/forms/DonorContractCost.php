<?php 

class Report_Form_DonorContractCost extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/donor-contract-cost-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Totais de Projeto';
    
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
        $this->removeElement('fk_id_dec');
        
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