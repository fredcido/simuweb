<?php 

class Report_Form_BankTransaction extends Report_Form_StandardSearch
{
    /**
     * @var string
     */
    const PATH = 'fefop/bank-transaction-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Movimentu Bankariu';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement('hidden', 'path')
		->setAttrib('class', 'no-clear')
		->setValue(self::PATH)
		->setDecorators(array('ViewHelper'));
        
        $elements[] = $this->createElement('hidden', 'title')
		->setAttrib('class', 'no-clear')
		->setValue(self::TITLE)
		->setDecorators(array('ViewHelper'));
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
        
        $this->removeElement('fk_id_dec');
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll();
	
	$optTypeTransaction = array();
	foreach ( $rows as $row )
	    $optTypeTransaction[$row['id_fefop_type_transaction']] = $row['acronym'] . ' - ' . $row['description'];
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_fefop_type_transaction' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optTypeTransaction )
			    ->setLabel( 'Tipu Transasaun' );
	
	$mapperFund = new Fefop_Model_Mapper_Fund();
	$funds = $mapperFund->fetchAll();
	
	$optFunds = array();
	foreach ( $funds as $fund )
	    $optFunds[$fund['id_fefopfund']] = $fund['name_fund'];
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_fefopfund' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optFunds )
			    ->setLabel( 'Fundu' );
	
	$optStatus = array();
	$optStatus['C'] = 'Consolidado';
	$optStatus['P'] = 'Pendente';
	$optStatus['A'] = 'Parcial';
	
	$elements[] = $this->createElement( 'multiselect', 'status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optStatus )
			    ->setLabel( 'Status' );
	
	$optPayment[''] = 'Hotu-hotu';
	$optPayment['C'] = 'Kontratu';
	$optPayment['F'] = 'Fundu';
	
	$elements[] = $this->createElement( 'select', 'bank_payment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optPayment )
			    ->setLabel( 'Pagamentu' );
        
        $this->addElements($elements);
    }
}