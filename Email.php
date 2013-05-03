<?php

/**
 * Email, manages user authentication
 *
 * @category   Core
 * @package    Core_Email
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Email
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Email
{
  /**
   * @var Zend Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_config = Zend_Registry::get('config');
  }

  /**
   * Main method to send emails. @todo: add attachment and to/cc/bcc functionality
   *
   * @param  string  $type    The email type
   * @param  array   $to      Array with people[name, email]
   * @param  array   $params  Array with parameters that are needed to generate the email body
   * @param  array   $subject Array with the email subjects
   * @param  array   $from    Array with the from-details
   * @param  boolean $bulk    If false (=default), a unique email is created for each user.
   *
   * @return
   */
  public function sendEmail($type, $to, $params, $subject, $from=array(), $bulk=false)
  {
    $this->_logger->info(__METHOD__);
    $params['host_url'] = $this->_config->host_url;
    if (!$bulk) {
      if (isset($to['name']) && isset($to['email'])) {
        $newTo[0] = $to;  // set into single item array format
        $to = $newTo;     // remove old to
      }
      $params['to_name']  = $to[0]['name'];
      $params['to_email'] = $to[0]['email'];
    }
    if (empty($from)) {
      $from = array(
        $this->_config->email->from->email,
        $this->_config->email->from->name
      );
    }
    // filter test emails
    $to = $this->_checkTestEmail($to);
    if (!empty($to)) {
      $this->_logger->notice(__METHOD__.' to: empty, all test-users??');
      return false;
    }
    switch ($this->_config->email->method) {
      case 'local':
          $n = $this->_sendZendMail($type, $to, $params, $subject, $from, $bulk);
        break;
      case 'all-mail':
        $n = $this->_sendAllMail($type, $to, $params, $subject, $from, $bulk);
        break;
    }
    $this->_logger->info(__METHOD__.' '.$n.' emails sent!');
    return $n;
  }

  /**
   * Method to send email using the Zend_Mail libraray
   *
   * @param  string  $type    The email type
   * @param  array   $to      Array with people[name, email]
   * @param  array   $params  Array with parameters that are needed to generate the email body
   * @param  string  $subject The email subject
   * @param  array   $from    Array with the from-details
   * @param  boolean $bulk    If false (=default), a unique email is created for each user.
   *
   * @return
   */
  protected function _sendZendMail($type, $to, $params, $subject, $from, $bulk)
  {
    // set items that are the same for each user. If subject has to be unique, the sendMail function
    // itself will need to be called multiple times
    $mail = new Zend_Mail();
    $mail->setSubject($subject);
    $mail->setFrom($from);
    // send all emails with the same body
    if ($bulk) {
      $mail->setBodyHtml($this->_createBody($type, $params));
      $mail->addTo($to);
      $mail->send();
    } else {
      foreach ($to as $user) {
        // create unique body per user
        $mail->setBodyHtml($this->_createBody($type, $params));
        $mail->addTo($user);
        $mail->send();
        // clear the recipient
        $mail->clearRecipients();
      }
    }
  }

  /**
   * Method to send email using the All-Mail service
   *
   * @param  string  $type    The email type
   * @param  array   $to      Array with people[name, email]
   * @param  array   $params  Array with parameters that are needed to generate the email body
   * @param  string  $subject The email subject
   * @param  array   $from    Array with the from-details
   * @param  boolean $bulk    If false (=default), a unique email is created for each user.
   *
   * @return
   */
  protected function _sendAllMail($type, $to, $params, $subject, $from)
  {

  }

  /**
   * Method to check for test emails (those starting with test++)
   *
   * @param  array $to Array with people[name, email]
   *
   * @return array same array with test emails removed.
   */
  protected function _checkTestEmail($to)
  {
    foreach ($to as $key => $value) {
      if (!empty($to['email'])) {
        if (substr($to['email'], 0, 6) == 'test++') {
          $this->_logger->notice(__METHOD__.' not sending to test email: '.print_r($to[$key], true));
          unset($to[$key]);
        }
      }
    }
    if (!empty($to)) {
      return $to;
    } else {
      return array();
    }
  }

  /**
   * Create the body for an email. Uses Views.
   *
   * @param  string $type   The email template type
   * @param  array  $params The params to be inserted in the email script
   *
   * @return string The html body
   */
  protected function _createBody($type, $params)
  {
    $this->_logger->info(__METHOD__);
    // create view
    $html = new Zend_View();
    $html->setScriptPath(APPLICATION_PATH.'/views/emails/');
    // assign variables (which can be strings/arrays/objects etc)
    foreach ($params as $key => $value) {
      $html->assign($key, $value);
    }
    $template = strtolower($type.'.phtml');
    $body = $html->render('_header.phtml');
    $body.= $html->render($template);
    $body.= $html->render('_footer.phtml');
    return $body;
  }

}