<?php

/**
 * Script to run Resque Workers.
 * Lots of useful info to be found here:
 * http://kamisama.me/2012/10/12/background-jobs-with-php-and-resque-part-4-managing-worker/
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
   *                       Can contain count: the number of processes to start
   *
   * @return string Any messages generated during starting up of workers
   */
  public function executeScript($params)
  {
    if(empty($params['queue'])) {
      return ("Set queue variable containing the csv list of queues to work.\n");
    } else {
      $queue = $params['queue'];
    }

    $config = Zend_Registry::get('config');

    $redisHost      = $config->resque->redis_host;
    $redisDb        = $config->resque->redis_db;
    $logLevel       = 0;
    $logging        = $config->resque->logging;
    $loggingNormal  = $config->resque->loggingNormal;
    $loggingVerbose = $config->resque->loggingVerbose;
    $interval       = $config->resque->interval;
    $count          = empty($params['count']) ? $config->resque->count : $params['count'];
    $message        = '';

    // set the connection to redis
    if (!empty($redisHost)) {
      if (empty($redisDb)) {
        Resque::setBackend($redisHost);
      } else {
        Resque::setBackend($redisHost, $redisDb);
      }
    }

    // set the logging
    if(!empty($logging) || !empty($loggingNormal)) {
      $logLevel = Resque_Worker::LOG_NORMAL;
    }
    else if(!empty($loggingVerbose)) {
      $logLevel = Resque_Worker::LOG_VERBOSE;
    }

    Resque_Redis::prefix($config->resque->prefix);

    if($count > 1) {
      for($i = 0; $i < $count; ++$i) {
        $pid = Core_Resque::fork();
        if($pid == -1) {
          return ("Could not fork worker ".$i."\n");
        }
        // Child, start the worker
        else if(!$pid) {
          $queues = explode(',', $queue);
          $worker = new Resque_Worker($queues);
          $worker->logLevel = $logLevel;
          $message.= '*** Starting worker '.$worker."\n";
          $worker->work($interval);
          break;
        }
      }
    }
    // Start a single worker
    else {
      $queues = explode(',', $queue);
      $worker = new Resque_Worker($queues);
      $worker->logLevel = $logLevel;

      $PIDFILE = getenv('PIDFILE');
      if ($PIDFILE) {
        try {
          file_put_contents($PIDFILE, getmypid());
        } catch (Exception $e) {
          return ('Could not write PID information to ' . $PIDFILE);
        }
      }

      $message.= '*** Starting worker '.$worker."\n";
      $worker->work($interval);
    }
    return $message;
  }
}