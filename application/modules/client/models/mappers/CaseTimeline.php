<?php

class Client_Model_Mapper_CaseTimeline extends App_Model_Abstract
{

    /**
     *
     * @var Model_DbTable_CaseTimeline
     */
    protected $_dbTable;

    /**
     *
     */
    protected function _getDbTable()
    {
        if (is_null($this->_dbTable)) {
            $this->_dbTable = new Model_DbTable_CaseTimeline();
        }

        return $this->_dbTable;
    }

    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTimelines($id)
    {
        $dbActionPlanTimeline = App_Model_DbTable_Factory::get('ActionPlanTimeline');
        $dbActionPlanBarrier = App_Model_DbTable_Factory::get('ActionPlanBarrier');
        $dbUser = App_Model_DbTable_Factory::get('SysUser');

        $select = $dbActionPlanTimeline->select()
            ->from(array('ct' => $dbActionPlanTimeline))
            ->setIntegrityCheck(false)
            ->join(
                array('ap' => $dbActionPlanBarrier),
                'ap.id_action_barrier = ct.fk_id_action_barrier',
                array()
            )
            ->join(
                array('u' => $dbUser),
                'u.id_sysuser = ct.fk_id_sysuser',
                array('user' => 'name')
            )
            ->where('ct.fk_id_action_plan = ?', $id)
            ->where('ct.fk_id_action_plan = ?', $id)
            ->order(array('date_insert DESC'));

        return $dbActionPlanTimeline->fetchAll($select);
    }

    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailTimeline($id)
    {
        $dbCaseTimelines = App_Model_DbTable_Factory::get('Case_Timeline');
        $dbUser = App_Model_DbTable_Factory::get('SysUser');

        $select = $dbCaseTimelines->select()
            ->from(array('cn' => $dbCaseTimelines))
            ->setIntegrityCheck(false)
            ->join(
                array('u' => $dbUser),
                'u.id_sysuser = cn.fk_id_sysuser',
                array(
                    'name',
                    'date_insert_formated' => new Zend_Db_Expr('DATE_FORMAT( cn.date_insert, "%d/%m/%Y %H:%i" )'),
                )
            )
            ->where('cn.id_case_note = ?', $id);

        return $dbCaseTimelines->fetchRow($select);
    }

    /**
     *
     * @return int|bool
     */
    public function delete()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {

            $dbCaseTimeline = App_Model_DbTable_Factory::get('Case_Timeline');

            $where = array(
                $dbAdapter->quoteInto('id_case_note = ?', $this->_data['id']),
            );

            $dbCaseTimeline->delete($where);

            $dbAdapter->commit();

            return true;

        } catch (Exception $e) {

            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
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

            if (empty($this->_data['id_case_note'])) {

                $mapperCase = new Client_Model_Mapper_Case();
                $case = $mapperCase->detailCase($this->_data['fk_id_action_plan']);

                $this->_data['fk_id_perdata'] = $case->fk_id_perdata;
                $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
            }

            // Save the Timeline
            $id = parent::_simpleSave();

            $dbAdapter->commit();
            return $id;

        } catch (Exception $e) {

            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
}
