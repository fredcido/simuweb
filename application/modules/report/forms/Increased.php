<?php

/**
 * 
 * @version $Id: Increased.php 373 2014-09-11 15:20:56Z frederico $
 */
class Report_Form_Increased extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/increased-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Kustos extras';
    
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