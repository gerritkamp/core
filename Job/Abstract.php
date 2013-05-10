<?php

/**
 * Abstract job
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
class Core_Job_Abstract
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
  }

  /**
   * Method to check whether all required arguments are submitted
   *
   * @param  array $args The submitted arguments
   *
   * @return boolean true if all ok, false otherwise
   */
  protected function _checkArgs($args)
  {
    $valid = true;
    // check for needed and valid params
    foreach ($this->_params as $key => $value) {
      if (!isset($args[$key])) {
        $valid = false;
        $this->_logger->err(__METHOD__.' required argument '.$key.' not provided!');
      }
    }
    return $valid;
  }

}