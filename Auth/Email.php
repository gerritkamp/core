<?php

/**
 * Core_Auth_Email, manages user authentication
 *
 * @category   Core
 * @package    Core_Auth
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Auth
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Auth_Email extends Core_Auth
{

  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Method to update a password from a token link
   *
   * @param  string  $token     A user token
   * @param  string  $password  The submitted password
   * @param  string  $confirm   The submitted confirm
   * @param  boolean $logUserIn Log the user in?
   *
   * @return boolean true upon success, false otherwise
   */
  public function updatePassword($token, $password, $confirm, $logUserIn=false)
  {
    $this->_logger->debug(__METHOD__);
    // check for valid password
    $length  = strlen($password);
    if ($length > 5) {
      if ($password === $confirm) {
        $userModel = new Core_Model_User();
        $userData = $userModel->getUserByToken($token);
        if (!empty($userData['id'])) {
          $password = $this->hashPassword($password, $userData['usersalt']);
          $updateData = array('password' => $password);
          $updated = $userModel->updateRecord($userData['id'], $updateData);
          if ($updated) {
            if ($logUserIn) {
              $this->_login($userData['id']);
            }
            return true;
          } else {
            $this->_logger->err(__METHOD__.' could not update user data');
            return false;
          }
        } else {
          $this->_logger->err(__METHOD__.' could not find user for token');
          return false;
        }
      } else {
        $this->_logger->err(__METHOD__.' passwords are not equal');
        return false;
      }
    } else {
      $this->_logger->err(__METHOD__.' passwords did not pass regex pattern');
      return false;
    }
  }

  /**
   * Method for logging a user in
   *
   * @param  string $email    The email
   * @param  string $password The password
   *
   * @return boolean true or false
   */
  public function login($email, $password)
  {
    $userModel = new Core_Model_User();
    $userData = $userModel->getUserByEmail($email, true);
    if (!empty($userData['password']) && !empty($userData['usersalt'])) {
      $hashedPassword = $userData['password'];
      $userSalt       = $userData['usersalt'];
      if ($this->verifyPassword($password, $hashedPassword, $userSalt)) {
        if ($this->_login($userData['id'])) {
          return true;
        } else {
          $this->_logger->err(__METHOD__.' something went wrong during logging in');
          return false;
        }
      } else {
        $this->_logger->warn(__METHOD__.' wrong password.');
        // implement policy for number of tries?
        return false;
      }
    } else {
      $this->_logger->warn(__METHOD__.' user not found for email: '.print_r($email, true));
      return false;
    }
  }

  /**
   * Method to hash a password
   *
   * @param string $password The unhashed password
   * @param string $userSalt The user salt
   *
   * @return string The hashed password
   */
  public function hashPassword($password, $userSalt)
  {
    $this->_logger->debug(__METHOD__);
    $appSalt = Zend_Registry::get('app_salt');
    return sha1($appSalt.$password.$userSalt);
  }

  /**
   * Method to verify a submitted password
   *
   * @param string $password       The unhashed password
   * @param string $hashedPassword The hashed password
   * @param string $userSalt       The user salt
   *
   * @return boolean true upon success, false otherwise
   */
  public function verifyPassword($password, $hashedPassword, $userSalt)
  {
    $this->_logger->debug(__METHOD__);
    $appSalt = Zend_Registry::get('app_salt');
    if (sha1($appSalt.$password.$userSalt) == $hashedPassword){
      return true;
    } else {
      return false;
    }
  }

}