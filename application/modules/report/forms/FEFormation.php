<?php

/**
 * 
 * @version $Id: FEFormation.php 448 2014-11-04 16:05:55Z frederico $
 */
class Report_Form_FEFormation extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/fe-formation-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Financiamento formasaun FE';
    
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