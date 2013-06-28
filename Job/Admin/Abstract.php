<?php

/**
 * Abstract Admin
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
abstract class Core_Job_Admin_Abstract
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var Session
   */
  protected $_session;

  /**
   * @var Redis client
   */
  protected $_redis;

  /**
   * @var Prefix string
   */
  protected $_prefix;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger  = Zend_Registry::get('logger');
    $this->_session = Zend_Registry::get('session');
    $this->_redis   = Zend_Registry::get('redis');
    $this->_prefix  = Zend_Registry::get('resque_prefix');
  }

}