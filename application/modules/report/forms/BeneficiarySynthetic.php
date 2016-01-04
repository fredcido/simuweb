<?php

/**
 * 
 * @version $Id: BeneficiarySynthetic.php 311 2014-08-20 14:26:42Z frederico $
 */
class Report_Form_BeneficiarySynthetic extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/beneficiary-synthetic-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Benefisiariu Sintetiku';
    
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