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

}