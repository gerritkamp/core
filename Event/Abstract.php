<?php

/**
 * Abstract event
 *
 * @category   Core
 * @package    Core_Event
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Event
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
abstract class Core_Event_Abstract
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var Logger
   */
  protected $_session;

  /**
   * @var Integer event type id
   */
  protected $_eventTypeId;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_session = Zend_Registry::get('session');
  }

  /**
   * Main method to process event. Force extending classes to define this method.
   *
   * @return mixed
   */
  abstract protected function processEvent($params=array());

  /**
   * Method to store an event in the database
   *
   * @param  integer $fromPersonId The person who caused the event
   * @param  array   $params       Any relevant params for the event
   * @return array
   */
  final public function storeEvent($fromPersonId = 0, $params = array())
  {
    $this->_logger->info(__METHOD__);
    // stores event in db
    $eventParams = array(
      'person_id'     => $fromPersonId,
      'event_type_id' => $this->_eventTypeId,
      'params'        => json_encode($params)
    );
    $eventModel = new Core_Model_Event();
    $eventData = $eventModel->insertNewRecord($eventParams);
    return $eventData;
  }

  /**
   * Method to save a job so workers can execute it
   *
   * @param  string $type The type of job
   * @param  array  $args The arguments for the job
   */
  final public function saveJob($args, $type=null)
  {
    $this->_logger->info(__METHOD__);
    $type = $type ? $type : $this->_type;
    // calls jobqueue to process event
    Resque::enqueue(Zend_Registry::get('account').':'.$type, 'Core_Job', $args);
  }
}