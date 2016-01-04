<?php

class App_View_Helper_FilterReport extends Zend_View_Helper_Abstract
{
    
    /**
     *
     * @param array $filters
     * @return string
     */
    public function filterReport( $filters = array() )
    {
	return $this->nameFilters( $filters );
    }
    
    /**
     *
     * @param array $filters
     * @return string 
     */
    public function nameFilters( $filters = array() )
    {
	$namedFilters = array();
	
	if ( !empty( $filters['date_start'] ) && !empty( $filters['date_finish'] ) )
	    $namedFilters[] = '<b>Periodu:</b> ' . $filters['date_start'] . ' <b>to\'o</b> ' .  $filters['date_finish'];
	
	if ( !empty( $filters['ceop'] ) )
	   $namedFilters[] = '  <b>CEOP:</b> ' . $filters['ceop'];
	
	if ( !empty( $filters['district'] ) )
	   $namedFilters[] = '  <b>Distritu:</b> ' . $filters['district'];
	
	if ( !empty( $filters['year'] ) )
	   $namedFilters[] = '  <b>Tinan:</b> ' . $filters['year'];
	
	if ( !empty($filters['year_start']) && !empty($filters['year_finish']) )
	    $namedFilters[] = ' <b>Tinan:</b> ' . $filters['year_start'] . ' <b>to\'o</b> '. $filters['year_finish'];
	
	if ( !empty( $filters['occupation'] ) )
	   $namedFilters[] = '  <b>Okupasaun:</b> ' . $filters['occupation'];
	
	if ( !empty( $filters['enterprise'] ) )
	   $namedFilters[] = '  <b>Empreza:</b> ' . $filters['enterprise'];
	
	if ( !empty( $filters['institute'] ) )
	   $namedFilters[] = '  <b>Ins. Ensinu:</b> ' . $filters['institute'];
	
	if ( array_key_exists( 'active', $filters ) )
	   $namedFilters[] = '  <b>Status:</b> ' . $this->view->nomenclature()->activeStatus( $filters['active'] );
	
	if ( !empty( $filters['country'] ) )
	   $namedFilters[] = '  <b>Nasaun:</b> ' . $filters['country'];
	
	if ( !empty( $filters['area'] ) )
	   $namedFilters[] = '  <b>Area Kursu:</b> ' . $filters['area'];
	
	if ( !empty( $filters['course'] ) )
	   $namedFilters[] = '  <b>Kursu:</b> ' . $filters['course'];
	
	if ( !empty( $filters['type_scholarity'] ) )
	   $namedFilters[] = '  <b>Tipu Kursu:</b> ' . $filters['type_scholarity'];
	
	if ( !empty( $filters['level_scholarity'] ) )
	   $namedFilters[] = '  <b>Nivel Kursu:</b> ' . $filters['level_scholarity'];
	
	if ( !empty( $filters['sector_industry'] ) )
	   $namedFilters[] = '  <b>Setor da Industria:</b> ' . $filters['sector_industry'];
	
	if ( !empty( $filters['type_enterprise'] ) )
	   $namedFilters[] = '  <b>Tipu Empreza:</b> ' . $filters['type_enterprise'];
	
	if ( !empty( $filters['category_school'] ) )
	   $namedFilters[] = '  <b>Categoria:</b> ' . $this->view->nomenclature()->scholarityCategory( $filters['category_school'] );
	
	if ( !empty( $filters['type_institution'] ) )
	   $namedFilters[] = '  <b>Tipu Institutisaun:</b> ' . $filters['type_institution'];
	
	if ( !empty( $filters['user'] ) )
	   $namedFilters[] = '  <b>Uzuariu:</b> ' . $filters['user'];
	
	if ( !empty( $filters['campaign_title'] ) )
	   $namedFilters[] = '  <b>Naran Kampanha:</b> ' . $filters['campaign_title'];
	
	if ( !empty( $filters['department'] ) )
	   $namedFilters[] = '  <b>Departamentu:</b> ' . $filters['department'];
	
	if ( !empty( $filters['campaign_type'] ) )
	   $namedFilters[] = '  <b>Tipu Kampanha:</b> ' . $filters['campaign_type'];
	
	if ( !empty( $filters['status_campaign'] ) )
	   $namedFilters[] = '  <b>Status Kampanha:</b> ' . $filters['status_campaign'];
	
	if ( !empty( $filters['fefop_program'] ) )
		$namedFilters[] = '  <b>Program:</b> ' . $filters['fefop_program'];
	
	if ( !empty( $filters['fefop_module'] ) )
		$namedFilters[] = '  <b>Module:</b> ' . $filters['fefop_module'];

	if ( !empty( $filters['scholarity_area'] ) )
		$namedFilters[] = '  <b>Area Formasaun:</b> ' . $filters['scholarity_area'];

	if ( !empty( $filters['ocupationtimor'] ) )
		$namedFilters[] = '  <b>Okupasaun:</b> ' . $filters['ocupationtimor'];

	if ( !empty( $filters['institution'] ) )
		$namedFilters[] = '  <b>Instituisaun:</b> ' . $filters['institution'];
	
	if ( !empty( $filters['user_inserted'] ) )
		$namedFilters[] = '  <b>Usuariu Rejistu:</b> ' . $filters['user_inserted'];
	
	if ( !empty( $filters['user_removed'] ) )
		$namedFilters[] = '  <b>Usuariu Libera:</b> ' . $filters['user_removed'];
	
	if ( !empty( $filters['status_description'] ) )
		$namedFilters[] = '  <b>Ativu:</b> ' . $filters['status_description'];
	
	if ( !empty( $filters['date_registration_ini'] ) )
		$namedFilters[] = '  <b>Data Rejistu Inisiu:</b> ' . $filters['date_registration_ini'];
	
	if ( !empty( $filters['date_registration_fim'] ) )
		$namedFilters[] = '  <b>Data Rejistu Final:</b> ' . $filters['date_registration_fim'];
	
	if ( !empty( $filters['num_year'] ) )
		$namedFilters[] = '  <b>Tinan:</b> ' . $filters['num_year'];
	
	if ( !empty( $filters['num_sequence'] ) )
		$namedFilters[] = '  <b>Sequence:</b> ' . $filters['num_sequence'];
	
	if ( !empty( $filters['minmaxamount'] ) )
		$namedFilters[] = '  <b>Kustu total:</b> ' . $filters['minmaxamount'];
	
	if ( !empty( $filters['fefop_status'] ) )
		$namedFilters[] = '  <b>Status:</b> ' . $filters['fefop_status'];

	if ( !empty( $filters['budget_category_type'] ) )
		$namedFilters[] = '  <b>Komponente:</b> ' . $filters['budget_category_type'];
	
	if ( !empty( $filters['description_type_fefopfund'] ) )
		$namedFilters[] = '  <b>Tipo de Fundo:</b> ' . $filters['description_type_fefopfund'];
	
	if (!empty($filters['beneficiary'])) {
		$namedFilters[] = ' <b>Benefisiariu:</b> ' . $filters['beneficiary'];
	}
	
	if (!empty($filters['type_beneficiary'])) {
		$namedFilters[] = ' <b>Tipu Benefisiariu:</b> ' . $filters['type_beneficiary'];
	}
	
	if (!empty($filters['fk_id_counselor'])) {
		$namedFilters[] = ' <b>Konselleiru:</b> ' . $filters['fk_id_counselor'];
	}
	
	return implode( ' <b>|</b> ', $namedFilters );
    }
}