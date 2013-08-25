<?php

/**
 * Job, manages asynchrounous jobs. Creates and destroys specific job objects
 *
 * @category   Core
 * @package    Core_Job
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Job
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Job
{

  /**
   * @var Core job object
   */
  protected $_object = null;

  /**
   * @var Job arguments
   */
  public $args = array();

  /**
   * @var Job queue name
   */
  public $queue = null;

  /**
   * @var Core Job Log
   */
  protected $_jobLog = null;

  /**
   * @var Core Job Log Id
   */
  protected $_jobLogId = null;

  /**
   * Method to setup object
   *
   * @return none
   */
  public function setUp()
  {
    // do I need to do a full autoload here?
    $this->_logger   = Zend_Registry::get('logger');
    $this->_logger->info(__METHOD__);
    // Factory to create the specific classs. Queuename ex: email, forgot_password
    if (empty($this->args['className'])) {
      $aName = explode('_', $this->queue);
      $className = 'Core_Job_';
      foreach ($aName as $part) {
        $className.= ucfirst($part);
      }
    } else {
      $className = $this->args['className'];
    }
    $this->_object = new $className($this->args);
    $this->_jobLog = new Core_Model_JobLog();
    $jobLogData = $this->_jobLog->addNewJob($className, $this->args);
    $this->_jobLogId = !empty($jobLogData['id']) ? $jobLogData['id'] : null;
  }

  /**
   * Performs the job
   *
   * @return None
   */
  public function perform()
  {
    try {
      $this->_object->perform($this->args);
      if (!empty($this->_jobLogId)) {
        $this->_jobLog->updateStatus($this->_jobLogId, 3);
      }
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.$e->getMessage());
      if (!empty($this->_jobLogId)) {
        $this->_jobLog->updateStatus($this->_jobLogId, 2);
      }
    }
  }

  /**
   * Method to tear object down
   *
   * @return none
   */
  public function tearDown()
  {
    // destroy the objects so that we don't run the risk of running jobs with wrong data
    $this->_jobLog = null;
    $this->_jobLogId = null;
    $this->_object = null;
  }

}