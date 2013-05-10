<?php

/**
 * Event, manages events
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
class Core_Event
{

  /**
   * Event types defined in core. To be extended by applications.
   * The values indicate whether event should trigger a job
   * @var array
   */
  protected $_eventTypes = array(
    'login' => 0,
    'logout' => 0,
    'register_user' => 1,
    'sign_up' => 1,
    'forgot_password' => 1,
  );

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var The event class
   */
  protected $_eventClass = null;

  /**
   * @var The event type
   */
  protected $_type = null;

  /**
   * Constructor
   *
   * @param $type The event type
   */
  public function __construct($type, $library='Core')
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_logger->info(__METHOD__);
    $this->_type = $type;
    if (in_array($type, array_keys($this->_eventTypes))) {

      // create the event class
      $aName = explode('_', $type);
      $className = $library.'_Event_';
      foreach ($aName as $part) {
        $className.= ucfirst($part);
      }
      $this->_eventClass = new $className();
    }
  }

  /**
   * Method to process the event
   *
   * @param  integer $fromPersonId The person causing the event
   * @param  array   $params       Submitted params
   *
   * @return array Default status array
   */
  public function processEvent($fromPersonId=0, $params=array())
  {
    $this->_logger->info(__METHOD__);

    // process the main event
    $params = $this->_eventClass->processEvent($fromPersonId, $params);
    if (empty($fromPersonId) && !empty($params['from_person_id'])) {
      // sometimes we get the from person only while processing event (forgot password, login)
      $fromPersonId = $params['from_person_id'];
    }

    if (is_array($params)) {
      //store the event in the database
      $eventData = $this->storeEvent($fromPersonId, $params);

      // some events trigger an asychronous job
      if ($this->_eventTypes[$this->_type]) {
        $this->saveJob($eventData);
      }
      return array('status' => 'success');
    } else {
      return array('status' => 'error', 'message' => 'Could not process event');
    }
  }

  /**
   * Method to store an event in the database
   *
   * @param  integer $fromPersonId The person who caused the event
   * @param  array   $params       Any relevant params for the event
   * @return array
   */
  public function storeEvent($fromPersonId = 0, $params = array())
  {
    $this->_logger->info(__METHOD__);
    // stores event in db
    $eventParams = array(
      'person_id'  => $fromPersonId,
      'event_type' => $this->_type,
      'params'     => json_encode($params)
    );
    $eventModel = new Core_Model_Event();
    $eventData = $eventModel->insertNewRecord($eventParams);
    $jobParams = $params;
    $jobParams['event_id'] = $eventData['id'];
    $jobParams['from_person_id'] = $fromPersonId;
    return $jobParams;
  }

  /**
   * Method to save a job so workers can execute it
   *
   * @param  string $type The type of job
   * @param  array  $args The arguments for the job
   */
  public function saveJob($args)
  {
    $this->_logger->info(__METHOD__);
    // calls jobqueue to process event
    Resque::enqueue($this->_type, 'Core_Job', $args);
  }

}