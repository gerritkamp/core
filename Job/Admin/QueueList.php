<?php

/**
 * Queuelist manages asynchrounous job queue lists
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
class Core_Job_Admin_QueueList extends Core_Job_Admin_Abstract
{

  /**
   * @var array with queue objects
   */
  protected $_listItems = array();

  /**
   * Method to get the process IDs with the queuename as key
   *
   * @return array Process IDs with the queuename as key
   */
  public function getProcessPidsByQueueName()
  {
    $workers = $this->_redis->smembers($this->_prefix.':workers');
    $pids = array();
    if ($workers) {
      foreach ($workers as $worker) {
        $parts = explode(':', $worker);
        $queueName = $parts[2].':'.$parts[3];
          $pids[$queueName][] = $parts[1];
      }
    }
    return $pids;
  }

  /**
   * Method to get all the queue names
   *
   * @return array with all queue names
   */
  public function getAllQueueNames()
  {
    return $this->_redis->smembers($this->_prefix.':queues');
  }

  /**
   * Method to get the details of each queue
   *
   * @return array list with details for each queue
   */
  public function getAllQueueDetails()
  {
    $queueNames = $this->getAllQueueNames();
    $queues = array();
    if ($queueNames) {
      foreach ($queueNames as $queueName) {
        $queue = new Core_Job_Admin_Queue($queueName);
        $pids = $queue->getProcessIds();
        $queue[] = array(
          'queuename' => $queueName,
          'account' => $queue->getAccountUlr(),
          'type' => $queue->getType(),
          'process_count' => $queue->getProcessCount(),
          'cpu' => $queue->getCpuUsage($pids, true),
          'mem' => $queue->getMemUsage($pids, true),
          'waiting' => $queue->getWaitingCount(),
          'failed' => $queue->getFailedCount(),
          'processed' => $queue->getProcessedCount()
          );
      }
    }
    return $queues;
  }

}