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
   * @var array
   */
  protected $_eventTypes = array(
    'login',
    'logout',
    'register_user',
    'sign_up',
    'forgot_password',
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
   * Constructor
   *
   * @param $type The event type
   */
  public function __construct($type, $library='Core');
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_logger->info(__METHOD__);
    if (in_array($type, $this->_eventTypes)) {

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
    if (in_array($type, $this->_eventTypes)) {
      // process the main event
      $params = $this->_eventClass->processEvent($fromPersonId, $params);
      if (empty($fromPersonId) && !empty($params['from_person_id'])) {
        // sometimes we get the from person only while processing event (forgot password, login)
        $fromPersonId = $params['from_person_id'];
      }

      if (is_array($params)) {
        //store the event in the database
        $eventData = $this->storeEvent($type, $fromPersonId, $params);

        // all other processing is done by workers
        $this->saveJob($type, $eventData);
        return array('status' => 'success');
      } else {
        return array('status' => 'error', 'message' => 'Could not process event');
      }
    }
  }

  /**
   * Method to store an event in the database
   *
   * @param  string  $type         The event type
   * @param  integer $fromPersonId The person who caused the event
   * @param  array   $params       Any relevant params for the event
   * @return array
   */
  public function storeEvent($type, $fromPersonId = 0, $params = array())
  {
    $this->_logger->info(__METHOD__);
    // stores event in db
    $eventParams = array(
      'person_id'  => $fromPersonId,
      'event_type' => $type,
      'params'     => json_encode($params)
    );
    $eventModel = new Core_Model_Event();
    $eventData = $eventModel->insertNewRecord($eventParams);
    $eventData['params'] = $params;
    $eventData['from_person_id'] = $fromPersonId;
    return $eventData;
  }

  /**
   * Method to save a job so workers can execute it
   *
   * @param  string $type The type of job
   * @param  array  $args The arguments for the job
   */
  public function saveJob($type, $args)
  {
    $this->_logger->info(__METHOD__);
    // calls jobqueue to process event
    Resque::enqueue($type, 'Core_Job', $args);
  }

}