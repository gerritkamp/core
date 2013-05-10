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
  protected $args = array();

  /**
   * @var Job queue name
   */
  protected $queue = null;

  /**
   * Method to setup object
   *
   * @return none
   */
  public function setUp()
  {
    // do I need to do a full autoload here?
    $this->_logger   = Zend_Registry::get('logger');
    // Factory to create the specific class based on the queue
    $aName = explode('_', $this->queue);
    $className = 'Core_';
    foreach ($aName as $part) {
      $className.= ucfirst($part);
    }
    $this->_object = new $className();
  }

  /**
   * Performs the job
   *
   * @return None
   */
  public function perform()
  {
    $this->_object->perform($this->args);
  }

  /**
   * Method to tear object down
   *
   * @return none
   */
  public function tearDown()
  {
    // destroy the object so that we don't run the risk of running jobs with wrong data
    $this->_object = null;
  }

}