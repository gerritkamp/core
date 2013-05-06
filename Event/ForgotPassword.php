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
class Core_Event_ForgotPassword implements Core_Event_Interface
{

  public function processEvent($email)
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
    // do email, internal, kpi, audit-trail as part of jobqueue
  }

}