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
class Core_Event_ForgotPassword
{

  protected function _processEvent($email)
  {
    // check if valid email address
    $filter = new Core_Filter_Email();
    $email = $filter->filter($email);
    $validator = new Zend_Validate_Email();
    if ($validator->isValid($email)) {
      // check if user exists
      $userModel = new Core_Model_User();
      // if so, reset token
      // send reset password email with reset link
    } else {
      $this->_logger->warn(__METHOD__.' forgot password with invalid email: '.$email);
      return false;
    }
    // store event in db
    // store kpi
    // send email
    // store internal
    // store audittrail
  }

}