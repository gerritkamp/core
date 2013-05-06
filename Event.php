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
   * Constructor
   */
  public function __construct($type, $fromPersonId, $params=array())
  {
    if (in_array($type, $this->_eventTypes)) {
      //store the event in the database
      $eventData = $this->storeEvent($type, $fromPersonId, $params);
      // all other processing is done by workers
      $this->saveJob($type, $eventData);
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
    // stores event in db
    $eventParams = array(
      'person_id'  => $fromPersonId,
      'event_type' => $type,
      'params'     => json_encode($params)
    );
    $eventModel = new Core_Model_Event();
    return $eventModel->insertNewRecord($eventParams);
  }

  /**
   * Method to save a job so workers can execute it
   *
   * @param  string $type The type of job
   * @param  array  $args The arguments for the job
   */
  public function saveJob($type, $args)
  {
    // calls jobqueue to process event
    Resque::enqueue($type, 'Core_Job', $eventData);
  }

}