<?php

class Default_Model_Mapper_DataFixer extends App_Model_Abstract
{
    
    /**
     *
     * @return boolean 
     */
    public function fixPhones()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $tables = array(
		/*
		'PerContact' => array(
		    'house_fone',
		    'job_fone',
		    'cell_fone'
		),
		*/
		'PerData' => array(
		    'client_fone'
		),
		/*
		'FEFPPartnerProposal' => array(
		    'contact_fone'
		),
		'Dec' => array(
		    'phone1',
		    'phone2'
		)
		 */
	    );
	    
	    $now = App_Cache::load( 'current_fix_phone' );
	    if ( empty( $now ) )
		$now = 0;
	    
	    $fixed = 0;
	    $step = 2000;
	    
	    echo 'Step: ' . $step . ' - now: ' . $now . "\n\r";
	    
	    foreach ( $tables as $table => $fields ) {
		
		$db = App_Model_DbTable_Factory::get( $table );
		$rows = $db->fetchAll( array(), array(), $step, $now );
		
		foreach ( $rows as $row ) {
		    
		    $altered = false;
		    foreach ( $fields as $field ) {
			
			$newPhone = App_General_String::cleanFone( $row[$field] );
			if ( !empty( $newPhone ) ) {
			    
			    $row[$field] = $newPhone;
			    $altered = true;
			}
		    }
		    
		    if ( $altered ) {
			
			$row->save();
			$fixed++;
		    }
		}
	    }
	    
	    $now += $step;
	    App_Cache::save( $now, 'current_fix_phone' );
	    
	    $dbAdapter->commit();
	    
	    echo number_format( $fixed, 0, '', '.' ) . ' Phones fixed'. "\n\r";
	    echo str_repeat( '-', 30 ) . "\n\r";
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    echo 'Error fixing phones: ' . $e->getMessage(). "\n\r";
	    return false;
	}
    }
}