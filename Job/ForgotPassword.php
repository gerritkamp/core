<?php
/**
 * Job to process a forgotten password
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
class Core_Job_ForgotPassword
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
   * Method to perform the job
   */
  public function perform()
  {
    // check for needed and valid params
    if (empty($this->args)) {
      $this->_logger->err(__METHOD__.' no args found for job');
    }

    $args = $this->args;
    if (empty($args['email'])) {
      $this->_logger->err(__METHOD__.' Email is missing');
    }

    // reset usertoken
    // send email
    // store kpi/audit/internal?

  }

}