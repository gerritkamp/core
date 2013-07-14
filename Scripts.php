<?php

/**
 * Scripts, manages various scripts
 *
 * @category   Core
 * @package    Core_Scripts
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Scripts
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Scripts
{

  public function __construct()
  {
    $maxLogLevel = 7;
    $writers = array('file');
    $this->_logger = New Core_Logger($writers, $maxLogLevel);
  }

  /**
   * Method to execute a script. Should be overwritten in child classes.
   *
   * @return string Message with results of script execution
   */
  public function executeScript()
  {

  }
}