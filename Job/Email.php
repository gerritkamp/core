<?php
/**
 * Job to send emails
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
class Core_Job_Email
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct($args = array())
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_args   = $args;
  }

  /**
   * Method to perform the job
   */
  public function perform()
  {
    // check for needed and valid params
    if (empty($this->_args)) {
      $this->_logger->err(__METHOD__.' no args found for job');
      return false;
    }

    $args = $this->_args;
    if (empty($args['email_type']) ||
      empty($args['to']) ||
      empty($args['subject']) ||
      empty($args['from']))
    {
      $msg = 'Missing params. Either email_type or to or subject or from missing from ';
      $this->_logger->err(__METHOD__.$msg.print_r($arg, true));
      return false;
    }

    // all good, continue and send email
    $type    = $args['email_type'];
    $to      = $args['to'];
    $params  = !empty($args['params']) ? $args['params'] : array();
    $subject = $args['subject'];
    $from    = $args['from'];
    $bulk    = !empty($args['bulk']) ? $args['bulk'] : false;
    $email   = new Core_Email();
    $email->sendEmail($type, $to, $params, $subject, $from, $bulk);
    return true;
  }

}