<?php

class Report_Model_Mapper_Admin extends App_Model_Mapper_Abstract
{
 
    /**
     *
     * @return \ArrayObject 
     */
    public function userReport()
    {
	$mapperUser = new Admin_Model_Mapper_SysUser();
	$users = $mapperUser->listAll( $this->_data['fk_id_dec'] );
	
	$data = array(
	    'rows' => $users
	);
	
	return $data;
    }
}