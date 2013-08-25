<?php
/**
 * The JobLog model.
 *
 * Status:
 * 1 = in process
 * 2 = failed
 * 3 = successfully processed
 *
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Model_JobLog extends Core_Model
{

  protected $_tableName = "core_job_log";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("tstamp", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("job_type_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("status_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("args", "text", "YES", "", "", "")
  );

  /**
   * Method to update an event status
   *
   * @param integer $id     The event ID
   * @param integer $status The event status
   *
   * @return null
   */
  public function updateStatus($id, $status)
  {
    $this->_logger->info(__METHOD__);
    $this->_logger->debug(__METHOD__.'. id: '.$id);
    if (is_numeric($status)) {
      $params = array(
        'tstamp' => time(),
        'status_id' => $status
      );
      $this->updateRecord($id, $params);
    }
  }

  /**
   * Method to add a new job
   *
   * @param string $className The classname for the new job
   * @param array  $args      The description for the new job
   *
   * @return null
   */
  public function addNewJob($className, $args)
  {
    $this->_logger->info(__METHOD__);
    $this->_logger->debug(__METHOD__.' args: '.print_r($args, true));
    try {
      $classNameParts = explode('_', $className);
      $type = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $classNameParts[2]));
      $jobTypeModel = new MvmSite_Model_JobType();
      $typeId = $jobTypeModel->getJobTypeIdByType($type);
      $jobLogData = array(
        'job_type_id' => $typeId,
        'args' => json_encode($args, JSON_NUMERIC_CHECK),
        'status_id' => 1
      );
      return $this->insertNewRecord($jobLogData);
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.print_r($e->getMessage()));
    }
  }

}