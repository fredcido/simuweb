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
            $this->_dbTable = new Model_DbTable_ActionPlanTimeline();
        }

        return $this->_dbTable;
    }

    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTimelines($barrier)
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
            ->where('ct.fk_id_action_barrier = ?', $barrier)
            ->order(array('ct.date_start ASC'));

        return $dbActionPlanTimeline->fetchAll($select);
    }

    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailTimeline($id)
    {
        $dbActionPlanTimeline = App_Model_DbTable_Factory::get('ActionPlanTimeline');
        $dbUser = App_Model_DbTable_Factory::get('SysUser');

        $select = $dbActionPlanTimeline->select()
            ->from(array('ctt' => $dbActionPlanTimeline))
            ->setIntegrityCheck(false)
            ->join(
                array('u' => $dbUser),
                'u.id_sysuser = ctt.fk_id_sysuser',
                array(
                    'user' => 'name',
                )
            )
            ->where('ctt.id_action_plan_timeline = ?', $id);

        return $dbActionPlanTimeline->fetchRow($select);
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

            $dbCaseTimeline = App_Model_DbTable_Factory::get('ActionPlanTimeline');

            $where = array(
                $dbAdapter->quoteInto('id_action_plan_timeline = ?', $this->_data['id']),
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
            if (empty($this->_data['id_action_plan_timeline'])) {
                $this->_data['fk_id_sysuser_created'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
            }

            if (!empty($this->_data['date_start'])) {
                $dateStart = new Zend_Date($this->_data['date_start']);
                $this->_data['date_start'] = $dateStart->toString('yyyy-MM-dd');
            }

            if (!empty($this->_data['date_end'])) {
                $dateFinish = new Zend_Date($this->_data['date_end']);
                $this->_data['date_end'] = $dateFinish->toString('yyyy-MM-dd');
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
