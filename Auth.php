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
   * Session namespace, something you probably want to override in applications
   *
   * @var string
   */
  protected $_sessionNamespace = 'core';

  /**
   * Constructor
   */
  public function __construct()
  {

  }

  public function logout()
  {
    // kill the session and direct user back to home
  }

  public function register()
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
  public function login($userId)
  {
    // get user roles and set them in the session
    // get user data and set it in the session
    // return true
  }
}