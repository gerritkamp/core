<?php

/**
 * Acl, manages which roles have access to which pages. Also builds the menu.
 *
 * @category   Core
 * @package    Core_Acl
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Acl
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Acl
{

  /**
   * Tree of structure [controller][action][roleaccess], with roleaccess.
   * being, for every role, 0=no access, 1=full access, 2=depends on context.
   * This example tree should be overridden for each specific application
   * @var array
   */
  protected $_aclTree = array(
    'index' => array(
      'index' => array(
        'show' => array(0,0,0),
        'access' => array(1,1,1),
      ),
    ),
  );

  /**
   * The various roles in the system. Just an example below. Override this in your own application.
   * @var array
   */
  protected $_roles = array(
   '0' => 'unauthenticated-user',
   '1' => 'logged-in-user',
   '2' => 'system-admin',
  );

  /**
   * Roles for someone who is not logged in. Should be overridden for each specific application.
   * @var array
   */
  protected $_notLoggedIn = array(1,0,0,);

  /**
   * Constructor
   */
  public function __construct($request)
  {
    $this->_request    = $request;
    $this->_controller = $request->getControllerName();
    $this->_action     = $request->getActionName();
  }

  /**
   * Getter for the roles
   *
   * @return array Array with roles
   */
  public function getRoles()
  {
    return $this->_roles;
  }

  /**
   * Getter for the not-logged-in roles
   *
   * @return array Array with roles
   */
  public function getNotLoggedInRoles()
  {
    return $this->_notLoggedIn;
  }

  /**
   * Method to create the menu, taking into account the roles a user has
   *
   * @param  array   $roles  The user roles
   * @param  integer $levels The number of levels for the menu. Default = 2.
   * @param  array   $params Additional params, optional
   *
   * @return array           Array with menu items (controller->action)
   */
  public function createMenu($roles, $levels=2, $params=array())
  {
    $menu = array();
    foreach ($this->_aclTree as $controller => $actions) {
      foreach ($actions as $action => $options) {
        $grants = $options['access'];
        if ($grants == 'none') {  // not granted to anyone so don't show
          // do nothing
        } elseif ($grants == 'all') {
          if ($options['show'] == 'none') { // granted to all but shown to none
            // again, do nothing
          } elseif($options['show'] == 'all') { // granted and shown to all
            $menu[$controller][$action] = 1;
          } elseif(is_array( $options['show'])) { // granted to all but shown to specific roles
            foreach ($options['show'] as $roleId => $show) {
              if (in_array($roleId, $roles) && $show==1){
                $menu[$controller][$action] = 1;
              }
            }
          }
        } elseif (is_array($grants)) { // grants to specific roles
          if ($options['show'] == 'none') { // granted to some but shown to none
            // do nothing
          } elseif ($options['show'] == 'all') { // granted to some but shown to all. Should not occur
            $this->_logger->err(
              __METHOD__.' granted to some but shown to all: '.$this->_controller.'/'.$this->_action
            );
            // log error but verify access based on roles anyway
            foreach ($grants as $roleId => $grant) {
              // for each role the user has, check if its granted, and if it should be shown
              if (in_array($roleId, $roles) && $grant==1) {
                $menu[$controller][$action] = 1;
              }
            }
          } elseif (is_array( $options['show'])) {
            foreach ($grants as $roleId => $grant) {
              // for each role the user has, check if its granted, and if it should be shown
              if (in_array($roleId, $roles) && $grant==1 && $options['show'][$roleId] == 1) {
                $menu[$controller][$action] = 1;
              }
            }
          }
        }
      }
    }
    return $menu;
  }

  /**
   * Method to check whether a user has access to this controller/action
   *
   * @param  array   $roles      Roles the user has
   * @param  array   $params     Additional params, optional
   *
   * @return boolean             Should user have access?
   */
  public function checkAccess($roles, $params=array())
  {
    $hasAccess = false;
    $controller = $this->_controller;
    $action     = $this->_action;
    // first check access is all
    if ($this->_aclTree[$controller][$action]['access'] == 'all') {
      $hasAccess = true;
    } elseif(is_array($this->_aclTree[$controller][$action]['access'])) {
      foreach ($roles as $roleId) {
        if (!empty($this->_aclTree[$controller][$action]['access'][$roleId])) {
          $access = $this->_aclTree[$controller][$action]['access'][$roleId];
          if ($access == 1) { // if one role is 1, user has access, period.
            $hasAccess = true;
          }
          if ($access == 2) {
            $maybeAccess = $this->_checkSpecialAccess(
              $roles, $controller, $action, $params
            );
            if ($maybeAccess) { // only set access to true if maybeAccess is true
              // do never set previously granted access to false again
              $hasAccess = true;
            }
          }
        }
      }
    }
    return $hasAccess;
  }

  /**
   * Method to check whether a user has access to a given controller/action
   *
   * @param  array   $roles      Roles the user has
   * @param  string  $controller Current controller
   * @param  string  $action     Current action
   * @param  array   $params     Additional params, optional
   *
   * @return boolean             Should user have access?
   */
  protected function _checkSpecialAccess($roles, $controller, $action, $params=array())
  {
    // this method should be overridden by some application specific rules
  }
}