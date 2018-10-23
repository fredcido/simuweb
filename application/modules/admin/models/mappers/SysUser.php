<?php

class Admin_Model_Mapper_SysUser extends App_Model_Abstract
{

    /**
     *
     * @var Model_DbTable_SysUser
     */
    protected $_dbTable;

    /**
     *
     */
    protected function _getDbTable()
    {
        if (is_null($this->_dbTable)) {
            $this->_dbTable = new Model_DbTable_SysUser();
        }

        return $this->_dbTable;
    }

    /**
     *
     * @return int|bool
     */
    public function save()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $row = $this->_checkLoginUser($this->_data);
        
            if (!empty($row)) {
                $this->_message->addMessage('Usuariu iha tiha ona.', App_Message::ERROR);
                return false;
            }

            if (empty($this->_data['password'])) {
                unset($this->_data['password']);
            } else {
                if ($this->_data['password'] != $this->_data['confirm_password']) {
                    $this->_message->addMessage('Password la hanesa.', App_Message::ERROR);
                    return false;
                }
            }
       
            $id = parent::_simpleSave();
        
            if (empty($this->_data['id_sysuser'])) {
                $history = 'INSERE USUARIU: %s DADUS PRINCIPAL - INSERE NOVO USUARIU';
            } else {
                $history = 'ALTERA USUARIU: %s DADUS PRINCIPAL - ALTERA USUARIU';
            }
        
            $history = sprintf($history, $this->_data['login']);
            $this->_sysAudit($history);
        
            $dbAdapter->commit();
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return boolean
     */
    public function editProfile()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            if (empty($this->_data['password'])) {
                unset($this->_data['password']);
            } else {
                if ($this->_data['password'] != $this->_data['confirm_password']) {
                    $this->_message->addMessage('Password la hanesa.', App_Message::ERROR);
                    return false;
                }
            }
       
            $id = parent::_simpleSave();
        
            $row = $this->detailUser($id);
            Zend_Auth::getInstance()->getStorage()->write($row);
        
            $dbAdapter->commit();
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkLoginUser()
    {
        $select = $this->_dbTable->select()->where('login = ?', $this->_data['login']);

        if (!empty($this->_data['id_sysuser'])) {
            $select->where('id_sysuser <> ?', $this->_data['id_sysuser']);
        }

        return $this->_dbTable->fetchRow($select);
    }
    
    /**
     *
     * @access 	public
     * @return 	boolean
     */
    public function login()
    {
        $valid = false;

        try {
            $authAdapter = new Zend_Auth_Adapter_DbTable(
                Zend_Db_Table_Abstract::getDefaultAdapter(),
                'SysUser',
                'login',
                'password'
        );

            $authAdapter->setIdentity($this->_data['username']);
            $authAdapter->setCredential($this->_data['password']);
        
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);

            if ($result->isValid()) {
                $resultSet = $authAdapter->getResultRowObject(null, 'password');

                // User status
                if (1 == $resultSet->active) {
            
            // Check if the user is related to Business plan, it can't log in
                    $dbUserBusiness = App_Model_DbTable_Factory::get('UserBusinessPlan');
                    $userBusiness = $dbUserBusiness->fetchRow(array( 'fk_id_sysuser = ?' => $resultSet->id_sysuser ));
            
                    if (!empty($userBusiness)) {
                        $auth->getStorage()->clear();
                    } else {
                        $this->_session->permissions = $this->fetchUserPermissions($resultSet->id_sysuser);
                        $auth->getStorage()->write($this->detailUser($resultSet->id_sysuser));

                        $valid = true;
                    }
                } else {
                    $auth->getStorage()->clear();
                }
            }

            return $valid;
        } catch (Exception $e) {
			echo '<pre>';var_dump($e);exit;
			
            return $valid;
        }
    }
    
    /**
     *
     * @param int $idUser
     * @return array
     */
    public function fetchUserPermissions($idUser)
    {
        // Fetch the user permissions
        $dbSysForm = App_Model_DbTable_Factory::get('SysForm');
        $dbSysUserHasForm = App_Model_DbTable_Factory::get('SysUserHasSysForm');
    
        $select = $dbSysUserHasForm->select()
                   ->from(array( 'suhf' => $dbSysUserHasForm ))
                   ->setIntegrityCheck(false)
                   ->join(
                    array( 'sf' => $dbSysForm ),
                    'sf.id_sysform = suhf.fk_id_sysform',
                    array()
                    )
                    ->where('suhf.fk_id_sysuser = ?', $idUser)
                    ->where('sf.active = ?', 1);
    
        $permissions = $dbSysUserHasForm->fetchAll($select);

        $finalPermissions = array();
        foreach ($permissions as $permission) {
            $finalPermissions[$permission->fk_id_sysform][] = $permission->fk_id_sysoperation;
        }
    
        return $finalPermissions;
    }
    
    /**
     *
     * @access 	public
     * @return 	boolean
     */
    public function loginExternal()
    {
        $valid = false;

        try {
            $auth = Zend_Auth::getInstance();
            $auth->getStorage()->clear();
            unset($this->_session->client);

            $mapperClient = new Client_Model_Mapper_Client();
            $selectClient = $mapperClient->selectClient();
        
            $selectClient->where('CONCAT( c.num_district, "-", c.num_subdistrict, "-", c.num_servicecode, "-", c.num_year, "-", c.num_sequence) = ?', $this->_data['evidence_card'])
           ->where('DATE_FORMAT( c.birth_date, "%d/%m/%Y" ) = ?', $this->_data['birth_date'])
           ->where('c.active = ?', 1);
        
            $dbPerData = App_Model_DbTable_Factory::get('PerData');
            $client = $dbPerData->fetchRow($selectClient);
    
            // Check is there is client with evidence card and date birth
            if (!empty($client)) {
                $userBusinessMapper = new Admin_Model_Mapper_UserBusiness();
                $userCeop = $userBusinessMapper->searchUserCeop($client->fk_id_dec);
        
                // Check if there is user defined for its ceop.
                if (!empty($userCeop)) {
                    $auth->getStorage()->write($this->detailUser($userCeop->id_sysuser));
                    $this->_session->client = $client;
                    $valid = true;
                }
            }

            return $valid;
        } catch (Exception $e) {
            return $valid;
        }
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailUser($id)
    {
        $dbUser = App_Model_DbTable_Factory::get('SysUser');
        $dbDec = App_Model_DbTable_Factory::get('Dec');
        $dbEduInstitution = App_Model_DbTable_Factory::get('FefpEduInstitution');
    
        $select = $dbUser->select()
             ->setIntegrityCheck(false)
             ->from(array( 'u' => $dbUser ))
             ->join(
                array( 'd' => $dbDec ),
                'd.id_dec = u.fk_id_dec',
                array( 'name_dec' )
             )
             ->joinLeft(
                array( 'ei' => $dbEduInstitution ),
                'ei.fk_id_sysuser = u.id_sysuser',
                array( 'institution', 'id_fefpeduinstitution' )
             )
             ->where('u.id_sysuser = ?', $id);
    
        return $dbUser->fetchRow($select);
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll($ceop = false)
    {
        $dbUser = App_Model_DbTable_Factory::get('SysUser');
        $dbDec = App_Model_DbTable_Factory::get('Dec');
    
        $select = $dbUser->select()
             ->setIntegrityCheck(false)
             ->from(array( 'u' => $dbUser ))
             ->join(
                array( 'd' => $dbDec ),
                'd.id_dec = u.fk_id_dec',
                array( 'name_dec' )
             )
             ->order(array( 'name' ));
    
        if ($ceop) {
            $select->where('u.fk_id_dec = ?', $ceop);
        }
    
        return $dbUser->fetchAll($select);
    }
    
    /**
     *
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit($description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE)
    {
        $data = array(
        'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::ADMIN,
        'fk_id_sysform'	    => Admin_Form_User::ID,
        'fk_id_sysoperation'    => $operation,
        'description'	    => $description
    );
    
        $mapperSysAudit = new Model_Mapper_SysAudit();
        $mapperSysAudit->setData($data)->save();
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $user
     * @return null|int
     */
    public static function userCeopToDistrict($user = false)
    {
        if (!$user) {
            $user = Zend_Auth::getInstance()->getIdentity();
        }
    
        $ceop = strtolower($user->name_dec);
    
        $dbAddDistrict = App_Model_DbTable_Factory::get('AddDistrict');
        $where = array(
        'LOWER(District) LIKE ?' => '%' . $ceop . '%'
    );
    
        $district = $dbAddDistrict->fetchRow($where);
        return empty($district) ? null : $district->id_adddistrict;
    }
}
