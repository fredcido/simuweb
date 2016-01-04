<?php

/**
 * 
 * @version $Id: BeneficiaryAnalytic.php 311 2014-08-20 14:26:42Z frederico $
 */
class Report_Form_BeneficiaryAnalytic extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/beneficiary-analytic-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Benefisiariu Analitiku';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        
        $this->getElement('path')->setValue(self::PATH);
        $this->getElement('title')->setValue(self::TITLE);
    }
}