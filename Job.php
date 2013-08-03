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
   * @var Integer Event ID
   */
  protected $_eventId = 0;

  /**
   * @var Core event object
   */
  protected $_event = null;

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
    // Factory to create the specific classs. Queuename ex: dev:email, dev:forgot_password
    if (empty($this->args['className'])) {
      $parts = explode(':', $this->queue);
      $aName = explode('_', $parts[1]);
      $className = 'Core_Job_';
      foreach ($aName as $part) {
        $className.= ucfirst($part);
      }
    } else {
      $className = $this->args['className'];
    }
    $this->_object = new $className($this->args);
    if (!empty($this->args['event_id'])) {
      $this->_eventId = $this->args['event_id'];
      $this->_event = new Core_Model_Event();
      $this->_event->updateStatus($this->_eventId, 1);
    }
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
      $this->_event->updateStatus($this->_eventId, 3);
    } catch (Exception $e) {
      $this->_logger->debug(__METHOD__.' error: '.$e->getMessage());
      $this->_event->updateStatus($this->_eventId, 2);
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
    $this->_event  = null;
    $this->_object = null;
  }

}