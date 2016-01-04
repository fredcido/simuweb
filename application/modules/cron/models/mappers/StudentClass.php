<?php

class Cron_Model_Mapper_StudentClass extends App_Model_Abstract
{    
    /**
     *
     * @return boolean 
     */
    public function sendClassIndmo()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $classes = $this->getDataClassIndmo();
	    if( empty( $classes ) )
		return true;

	    $dataIndmo = array( 'classes' => serialize( $classes ) );
	    $response = App_Util_Indmo::request( 'api/class', $dataIndmo );
	    
	    $dbStudentClassSent = App_Model_DbTable_Factory::get( 'StudentClass_Sent' );
	    $trainingProvider = array();
	    foreach ( $classes as $class ) {

		if ( !empty( $response['classnotfound'] ) && in_array( $class['id'], $response['classnotfound'] ) ) {
		    $trainingProvider[] = $class['id_training_provider'];
		    continue;
		}
		
		$where = array(
		    'fk_id_fefpstudentclass = ?' => $class['id'],
		    'sent = ?'			 => 0
		);

		$row = $dbStudentClassSent->fetchRow( $where );
		$row->date_sent = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$row->sent = empty( $response['status'] ) ? 0 : 1;

		if ( !empty( $response['msg'] ) )
		    $row->log = $response;

		$row->save();
	    }
	    
	    if ( !empty( $trainingProvider ) ) {
	    
		// Search the user who must receive notes when training provider was not found at INDMO
	       $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	       $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::TP_NOT_FOUND );

	       $noteModelMapper = new Default_Model_Mapper_NoteModel();
	       $noteMapper = new Default_Model_Mapper_Note();

		$dataNote = array(
		    'title'   => 'SENTRU FORMASAUN LA REJISTRADU',
		    'level'   => 0,
		    'message' => $noteModelMapper->getTrainingProviderNotFound( $trainingProvider ),
		    'users'   => $users
		);

		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    echo 'Error sending classes: ' . $e->getMessage();
	    return false;
	}
    }
    
    /**
     *
     * @return array
     */
    public function getDataClassIndmo()
    {
	$dbStudentClassSent = App_Model_DbTable_Factory::get( 'StudentClass_Sent' );
	$rows = $dbStudentClassSent->fetchAll( array( 'sent = ?' => 0 ) );

	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();

	$classes = array();
	foreach ( $rows as $row ) {

	    $class = $mapperStudentClass->detailStudentClass( $row->fk_id_fefpstudentclass );
	    $course = $mapperScholarity->fetchRow( $class->fk_id_perscholarity );

	    $dataClass = array(
		'id'			=> $class->id_fefpstudentclass,
		'title'			=> $class->class_name,
		'start_date'		=> $class->start_date,
		'finish_date'		=> $class->schedule_finish_date,
		'qualification'		=> $course->remote_id,
		'training_provider'	=> $class->num_register,
		'id_training_provider'	=> $class->fk_id_fefpeduinstitution
	    );

	    $students = $mapperStudentClass->listClientClass( $row->fk_id_fefpstudentclass );

	    $studentsClass = array();
	    foreach ( $students as $student ) {

		$dataStudent = array(
		    'id'		=> $student->id_perdata,
		    'first_name'	=> $student->first_name,
		    'second_name'	=> $student->medium_name,
		    'evidence'		=> Client_Model_Mapper_Client::buildNumRow( $student ),
		    'last_name'		=> $student->last_name,
		    'district'		=> $student->num_district,
		    'sub_district'	=> $student->num_subdistrict,
		    'birth_date'	=> $student->birth_date,
		    'gender'		=> $student->gender,
		    'email'		=> $student->email,
		    'tel'		=> $student->client_fone,
		    'result'		=> $student->status_class,
		    'competencies'	=> array()
		);

		$competencies = $mapperStudentClass->listCompetencyClass( $row->fk_id_fefpstudentclass, $student->id_perdata );
		foreach ( $competencies as $competency ) {

		    $dataStudent['competencies'][] = array(
			'id'     => $competency->id_external,
			'code'   => $competency->external_code,
			'result' => $competency->status
		    );
		}

		$studentsClass[] = $dataStudent;
	    }

	    $dataClass['students'] = $studentsClass;
	    $classes[] = $dataClass;
	}
	
	return $classes;
    }
}
