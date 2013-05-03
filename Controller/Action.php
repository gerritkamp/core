<?php
/**
 * The core controller
 *
 * @category   Core
 * @package    Core_Controller
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Controller
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Controller_Action extends Zend_Controller_Action
{

  /**
   * @var acl object
   */
  protected $_acl = null;

  /**
   * Session object, useful in each controller
   * @var Zend_Session
   */
  protected $_session = null;

  /**
   * Logger object, also useful in each controller
   * @var Zend_Logger
   */
  protected $_logger = null;

  public function __construct(
    Zend_Controller_Request_Abstract $request,
    Zend_Controller_Response_Abstract $response,
    array $invokeArgs = array()
  )
  {
    parent::__construct($request, $response, $invokeArgs);

    // set the session
    $this->_session = Zend_Registry::get('session');

    // set the logger
    $this->_logger = Zend_Registry::get('logger');
  }

}