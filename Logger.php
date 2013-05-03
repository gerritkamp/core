<?php
/**
 * The logger
 *
 * @category   Core
 * @package    Core_Log
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Log
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Logger extends Zend_Log
{
  /**
   * array with writers
   */
  protected $_logWriters = array();

  public function __construct($writers=array())
  {
    parent::__construct();
    $this->_logWriters = $writers;
    $this->_setWriters();
  }

  protected function _setWriters()
  {
    if ($this->_logWriters) {
      foreach ($this->_logWriters as $writer) {
        switch ($writer) {
          case 'file':
            $now = time();
            $format = date('H:i:s').' %priorityName% %message% '.PHP_EOL;
            $formatter = new Zend_Log_Formatter_Simple($format);
            $file = ROOT_PATH.'/logs/'.date('Y-m-d').'.log';
            $writer = new Zend_Log_Writer_Stream($file);
            $this->addWriter($writer);
          break;
          case 'firebug':
            $writer = new Zend_Log_Writer_Firebug();
            $this->addWriter($writer);
          break;
        }
      }
    }
  }

  /**
   * Method to write the message to the logs
   *
   * @param  string $message  The log message
   * @param  [type] $priority Log priority (
   *                          EMERG   = 0;  // Emergency: system is unusable
   *                          ALERT   = 1;  // Alert: action must be taken immediately
   *                          CRIT    = 2;  // Critical: critical conditions
   *                          ERR     = 3;  // Error: error conditions
   *                          WARN    = 4;  // Warning: warning conditions
   *                          NOTICE  = 5;  // Notice: normal but significant condition
   *                          INFO    = 6;  // Informational: informational messages
   *                          DEBUG   = 7;  // Debug: debug messages)
   *
   * @return None
   */
  protected function _writeMessage($message, $priority)
  {
    if (in_array('firebug', $this->_logWriters)) {
      $request = new Zend_Controller_Request_Http();
      $response = new Zend_Controller_Response_Http();
      $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
      $channel->setRequest($request);
      $channel->setResponse($response);

      // Start output buffering
      ob_start();

      // Now you can make calls to the logger
      $this->log($message, $priority);

      // Flush log data to browser
      $channel->flush();
      $response->sendHeaders();
    } else {
      $this->log($message, $priority);
    }
  }

  public function emerg($message)
  {
    $this->_writeMessage($message, Zend_Log::EMERG);
  }

  public function alert($message)
  {
    $this->_writeMessage($message, Zend_Log::ALERT);
  }

  public function crit($message)
  {
    $this->_writeMessage($message, Zend_Log::CRIT);
  }

  public function err($message)
  {
    $this->_writeMessage($message, Zend_Log::ERR);
  }

  public function warn($message)
  {
    $this->_writeMessage($message, Zend_Log::WARN);
  }

  public function notice($message)
  {
    $this->_writeMessage($message, Zend_Log::NOTICE);
  }

  public function info($message)
  {
    $this->_writeMessage($message, Zend_Log::INFO);
  }

  public function debug($message)
  {
    $this->_writeMessage($message, Zend_Log::DEBUG);
  }
}