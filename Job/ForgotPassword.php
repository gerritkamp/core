<?php
/**
 * Job to process a forgotten password.
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
class Core_Job_ForgotPassword extends Core_Job_Abstract implements Core_Job_Interface
{

  /**
   * @var array with params that need to be provided, and values for testing
   */
  protected $_params = array(
    'event_id' => 1,
    'from_person_id' => 1,
    'token' => 'aabbcc'
  );

  /**
   * Method to perform the job
   */
  public function perform($args=array())
  {
    $argsOk = $this->_checkArgs($args);

    if ($argsOk) {
      // process job
      // send email
      $userModel = new Core_Model_User();
      $userData = $userModel->getUserById($args['from_person_id']);
      $type    = 'forgot_password';
      $to      = array(
        'email' => $userData['email'],
        'name'  => $userData['first_name'].' '.$userData['last_name']
      );
      $args['reset_link'] = 'login/resetpassword/resethash/'.$userData['token'];
      $params  = $args;
      $subject = 'Reset password';
      $email   = new Core_Email();
      $email->sendEmail($type, $to, $params, $subject);

    // store kpi/audit/internal?
    } else {
      // burry job
    }
  }

}