<?php

/**
 * Script to run Resque Workers
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
class Core_Scripts_ResqueWorker extends Core_Scripts
{

  /**
   * Main method to start a worker
   *
   * @param  array $params The params for the worker.
   *                       Must contain 'queue'. Comma separated list of queues, ex: "email, pdf"
   *
   * @return string Any messages generated during starting up of workers
   */
  public function executeScript($params)
  {
    if(empty($params['queue'])) {
      return ("Set queue variable containing the list of queues to work.\n");
    }

    $config = Zend_Registry::get('config');

    $REDIS_BACKEND = getenv('REDIS_BACKEND');
    $REDIS_BACKEND_DB = getenv('REDIS_BACKEND_DB');
    if(!empty($REDIS_BACKEND)) {
      if (empty($REDIS_BACKEND_DB)) 
        Resque::setBackend($REDIS_BACKEND);
      else
        Resque::setBackend($REDIS_BACKEND, $REDIS_BACKEND_DB);
    }

    $logLevel = 0;
    $LOGGING = getenv('LOGGING');
    $VERBOSE = getenv('VERBOSE');
    $VVERBOSE = getenv('VVERBOSE');
    if(!empty($LOGGING) || !empty($VERBOSE)) {
      $logLevel = Resque_Worker::LOG_NORMAL;
    }
    else if(!empty($VVERBOSE)) {
      $logLevel = Resque_Worker::LOG_VERBOSE;
    }

    $APP_INCLUDE = getenv('APP_INCLUDE');
    if($APP_INCLUDE) {
      if(!file_exists($APP_INCLUDE)) {
        die('APP_INCLUDE ('.$APP_INCLUDE.") does not exist.\n");
      }

      require_once $APP_INCLUDE;
    }

    $interval = 5;
    $INTERVAL = getenv('INTERVAL');
    if(!empty($INTERVAL)) {
      $interval = $INTERVAL;
    }

    $count = 1;
    $COUNT = getenv('COUNT');
    if(!empty($COUNT) && $COUNT > 1) {
      $count = $COUNT;
    }

    $PREFIX = getenv('PREFIX');
    if(!empty($PREFIX)) {
        fwrite(STDOUT, '*** Prefix set to '.$PREFIX."\n");
        Resque_Redis::prefix($PREFIX);
    }

    if($count > 1) {
      for($i = 0; $i < $count; ++$i) {
        $pid = Resque::fork();
        if($pid == -1) {
          die("Could not fork worker ".$i."\n");
        }
        // Child, start the worker
        else if(!$pid) {
          $queues = explode(',', $QUEUE);
          $worker = new Resque_Worker($queues);
          $worker->logLevel = $logLevel;
          fwrite(STDOUT, '*** Starting worker '.$worker."\n");
          $worker->work($interval);
          break;
        }
      }
    }
    // Start a single worker
    else {
      $queues = explode(',', $QUEUE);
      $worker = new Resque_Worker($queues);
      $worker->logLevel = $logLevel;

      $PIDFILE = getenv('PIDFILE');
      if ($PIDFILE) {
        file_put_contents($PIDFILE, getmypid()) or
          die('Could not write PID information to ' . $PIDFILE);
      }

      fwrite(STDOUT, '*** Starting worker '.$worker."\n");
      $worker->work($interval);
    }


  }
}