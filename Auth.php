<?php

/**
 * Auth, manages user authentication
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
class Core_Auth
{
  /**
   * @var Zend_Session
   */
  protected $_session = null;

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger  = Zend_Registry::get('logger');
    $this->_session = Zend_Registry::get('session');
  }

  /**
   * Method to log a user out
   *
   * @return boolean true
   */
  public function logout()
  {
    $this->_logger->info(__METHOD__);
    $event = new Core_Event('logout');
    $this->_session->userRoles = null;
    $this->_session->userData  = null;
    $this->_session->userId    = null;
    return true;
  }

  protected function _register()
  {
    // register a new user
  }

  /**
   * Method to log a user in. This assumes the user has already been authenticated.
   * The authentication should happen in classes that extend this class.
   *
   * @param  integer $userId Just the user ID
   *
   * @return boolean Whether the user was successfully logged-in or not
   */
  protected function _login($userId)
  {
    $this->_logger->info(__METHOD__);
    // get user roles and set them in the session
    $userRoleModel = new Core_Model_UserRole();
    $roles = $userRoleModel->getUserRoles($userId);
    $this->_logger->debug(__METHOD__.' roles: '.print_r($roles, true));
    $this->_session->userRoles = $roles;
    // get user data and set it in the session
    $userModel = new Core_Model_User();
    $userData = $userModel->getUserById($userId);
    $this->_logger->debug(__METHOD__.' userData: '.print_r($userData, true));
    $this->_session->userData = $userData;
    $this->_session->userId = $userId;
    // return true
    return true;
  }
}