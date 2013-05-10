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
class Core_Event_ForgotPassword extends Core_Event_Abstract implements Core_Event_Interface
{

  /**
   * Method to process the event
   *
   * @param  integer $fromPersonId The person who caused the event
   * @param  array   $params       Submitted params
   *
   * @return mixed false upon error, array with params upon success
   */
  public function processEvent($fromPersonId=0, $params=array())
  {
    if (empty($params['email'])) {
      $this->_logger->warn(__METHOD__.' forgot password without email');
      return false;
    }
    // check if valid email address
    $email = $params['email'];
    $filter = new Core_Filter_Email();
    $email = $filter->filter($email);
    $validator = new Zend_Validate_EmailAddress();
    if ($validator->isValid($email)) {
      // check if user exists
      $userModel = new Core_Model_User();
      $userData = $userModel->getUserByEmail($email);
      // if so, reset token
      if (!empty($userData['id'])) {
        $newToken = $userModel->resetToken($userData['id']);
        if (empty($newToken)) {
          $this->_logger->warn(__METHOD__.' forgot password with unknown email: '.$email);
          return false;
        }
      } else {
        $this->_logger->warn(__METHOD__.' forgot password with unknown email: '.$email);
        return false;
      }
    } else {
      $this->_logger->warn(__METHOD__.' forgot password with invalid email: '.$email);
      return false;
    }

    // all ok, return params
    return array('token' => $newToken, 'from_person_id' => $userData['id']);
  }

}