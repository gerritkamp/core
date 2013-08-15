<?php

/**
 * Manages logout
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
class Core_Event_Logout extends Core_Event_Abstract
{

  protected $_type = 'logout';

  /**
   * @var integer event_type_id
   */
  protected $_eventTypeId = 9;

  /**
   * Method to process the event
   *
   * @param array $params The array with event parameters. May contain from_user_id
   *
   * @return array status
   */
  public function processEvent($params=array())
  {
    $this->_logger->info(__METHOD__);
    $return = array('status' => 'error');
    if (empty($params['user_id'])) {
      $this->_logger->err(__METHOD__.' no user id given');
      return $return;
    }
    $auth = new Core_Auth();
    $auth->logout();
    $this->storeEvent($params['user_id']);
    $return['status'] = 'success';
    return $return;
  }

}