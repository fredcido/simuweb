<?php

/**
 * 
 * @version $Id: Totalizer.php 366 2014-09-09 06:30:39Z frederico $
 */
class Report_Form_Totalizer extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/totalizer-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Financiamento total de contrato';
    
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