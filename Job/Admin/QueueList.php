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
    $this->_logger->info(__METHOD__);
    $queueNames = $this->getAllQueueNames();
    $queues = array();
    if ($queueNames) {
      foreach ($queueNames as $queueName) {
        $queue = new Core_Job_Admin_Queue($queueName);
        $pids = $queue->getProcessIds();
        $queues[] = array(
          'queuename' => $queueName,
          'account' => $queue->getAccountUlr(),
          'type' => $queue->getType(),
          'process_count' => $queue->countProcesses(),
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

  /**
   * Method to get selected details of selected queues
   *
   * @param array $queueNames The queue names selection
   * @param array $fieldNames The field names selection
   *
   * @return array list with selected queue details
   */
  public function getSelectedQueueDetails($queueNames, $fieldNames)
  {
    $this->_logger->info(__METHOD__);
    $queues = array();
    if ($queueNames) {
      foreach ($queueNames as $queueName) {
        $queue = new Core_Job_Admin_Queue($queueName);
        $pids = $queue->getProcessIds();
        $result = array();
        foreach ($fieldNames as $field) {
          $this->_logger->debug(__METHOD__.' field: '.$field);
          switch ($field) {
            case 'account':
              $result[$field] = $queue->getAccountUlr();
              break;
            case 'type':
              $result[$field] = $queue->getType();
              break;
            case 'process_count':
              $result[$field] = $queue->countProcesses();
              break;
            case 'cpu':
              $result[$field] = $queue->getCpuUsage($pids, true);
              break;
            case 'mem':
              $result[$field] = $queue->getMemUsage($pids, true);
              break;
            case 'waiting':
              $result[$field] = $queue->getWaitingCount();
              break;
            case 'failed':
              $result[$field] = $queue->getFailedCount();
              break;
            case 'processed':
              $result[$field] = $queue->getProcessedCount();
              break;
            case 'in_process': // @todo find number of items being processed
              $result[$field] = 0;
              break;
            default:
              $result[$field] = 'invalid';
              break;
          }
        }
        $queues[$queueName] = $result;
      }
    }
    $this->_logger->debug(__METHOD__.' queues: '.print_r($queues, true));
    return $queues;
  }

  /**
   * Method to get paged data
   *
   * @param string  $sortColumn The column to sort on
   * @param string  $direction  The direction to sort on
   * @param integer $length     The length of the array to be returned
   * @param integer $start      The start of the array to be returned
   * @param array   $queueNames If given, use these queuenames
   * @param array   $fieldNames If given, return these fieldnames
   *                            (otherwise its just the sortcolum)
   *
   * @return array Paged queue data
   */
  public function getPagedData(
    $sortColumn, $direction, $length, $start, $queueNames=array(), $fieldNames=array()
  )
  {

  }

}