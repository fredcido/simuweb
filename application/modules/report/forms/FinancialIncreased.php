<?php

/**
 * 
 * @version $Id: FinancialIncreased.php 373 2014-09-11 15:20:56Z frederico $
 */
class Report_Form_FinancialIncreased extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/financial-increased-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Fianciamento Kustos extras';
    
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