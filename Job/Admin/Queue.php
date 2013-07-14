<?php

/**
 * Queue, manages asynchrounous job queues.
 *
 * Resque data model:
 *
 * resque:queues
 *   set: members are the names of the various queues. Format 'account':'type'
 *   Example: "stevens:email"
 *
 * resque:workers
 *   set: members are the processes. Format: servername:process_id:queuename.
 *   Example: "gerrit-VirtualBox:2893:stevens:email"
 *
 * resque:failed
 *   list: members are the details of the failed jobs.
 *   Example: "{"failed_at":"Thu May 09 21:24:05 EDT 2013",
 *             "payload":{
 *               "class":"Core_Job",
 *               "args":[{
 *                 "token":"31658281a91ec2b100ca74506e3dad3085df1a55",
 *                 "from_person_id":"1",
 *                 "event_id":"5"
 *                }],
 *               "id":"24cf6f18705998940e9cc275b9c0212e"},
 *             "exception":"Resque_Job_DirtyExitException",
 *             "error":"Job exited with exit code 255",
 *             "backtrace":
 *               ["#0 \home\gerrit\git\core\Scripts\ResqueWorker.php(82): Resque_Worker->work('5')",
 *                "#1 \home\gerrit\git\mvm\scripts\script.php(137): Core_Scripts_ResqueWorker->executeScript(Array)",
 *                "#2 {main}
 *               "],
 *             "worker":"gerrit-VirtualBox:2893:forgot_password",
 *             "queue":"forgot_password"}"
 *
 * resque:queue:somename
 *   list: members are the individual jobs waiting to be picked up.
 *   Example: "{""class":"Core_Job",
 *               "args":[{
 *                 "token":"31658281a91ec2b100ca74506e3dad3085df1a55",
 *                 "from_person_id":"1",
 *                 "event_id":"5"
 *                }],
 *                "id":"b29a0518c7a0e7b4ec4659afbd554e40"}"
 *
 * resque:stat:processed/failed/
 *   type: string value: count of jobs that are processed/failed
 *
 * resque:stat:processed/failed:queuename
 *   type: string value: count of jobs that are processed/failed in this particular queue
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
class Core_Job_Admin_Queue extends Core_Job_Admin_Abstract
{

  /**
   * @var string Account URL
   */
  protected $_accountUrl = null;

  /**
   * @var string Type
   */
  protected $_type = null;

  /**
   * Constructor
   *
   * @param string $queueName The queue name of this queue
   */
  public function __construct($queueName='')
  {
    if ($queueName) {
      $parts = explode(':'. $queueName);
      $this->setAccountUrl($parts[0]);
      $this->setType($parts[1]);
    }
    parent::__construct();
  }

  /**
   * Method to set the account url
   *
   * @param string $accountUrl The account url
   *
   * @return null
   */
  public function setAccountUrl($accountUrl)
  {
    $this->_accountUrl = $accountUrl;
  }

  /**
   * Method to get an account URL name
   *
   * @return string The account
   */
  public function getAccountUrl()
  {
    return $this->_accountUrl;
  }

  /**
   * Method to set the type
   *
   * @param string $type The queue type
   *
   * @return null
   */
  public function setType($type)
  {
    $this->_type = $type;
  }

  /**
   * Method to get the type
   *
   * @return string The type of this queue
   */
  public function getType()
  {
    return $this->_type;
  }

  /**
   * Method to get the queuename
   *
   * @return string The queue name
   */
  public function getQueueName()
  {
    if (!empty($this->_accountUrl) && !empty($this->_type)) {
      return $this->_accountUrl.':'.$this->_type;
    }
    return null;
  }

  /**
   * Method to get the number of jobs in the waiting queue
   *
   * @return integer Number of waiting jobs
   */
  public function getWaitingCount()
  {
      return $this->_redis->llen($this->_prefix.':queue:'.$this->getQueueName());
  }

  /**
   * Method to get the number of failed jobs
   *
   * @return integer Number of failed jobs
   */
  public function getFailedCount()
  {
    return $this->_redis->get($this->_prefix.':stat:failed:'.$this->getQueueName());
  }

  /**
   * Method to get the count of processed jobs
   *
   * @return integer Number of processed jobs
   */
  public function getProcessedCount()
  {
    return $this->_redis->get($this->_prefix.':stat:processed:'.$this->getQueueName());
  }

  /**
   * Method to get the process IDs
   *
   * @return array with process IDs
   */
  public function getProcessPids()
  {
    $workers = $this->_redis->smembers($this->_prefix.':workers');
    $pids = array();
    if ($workers) {
      $thisQueueName = $this->getQueueName();
      foreach ($workers as $worker) {
        $parts = explode(':', $worker);
        $queueName = $parts[2].':'.$parts[3];
        if ($queueName == $thisQueueName) {
          $pids[] = $parts[1];
        }
      }
    }
    return $pids;
  }

  /**
   * Method to count the number of processes
   *
   * @param array $pids The process ids. Optional. If empty, process IDs from queue will be used.
   *
   * @return integer count of processes
   */
  public function countProcesses($pids=array())
  {
    if (!$pids) {
      $pids = $this->getProcessPids();
    }
    return count($pids);
  }

  /**
   * Method to get the CPU usage of the processes in this queue
   *
   * @param array   $pids  The process ids. Optional. If empty, process IDs from queue will be used.
   * @param boolean $total Return one total number. Default false
   *
   * @return array with cpu usage per process ID
   */
  public function getCpuUsage($pids=array(), $total=false)
  {
    if (!$pids) {
      $pids = $this->getProcessPids();
    }
    $cpu = array();
    $sum = 0;
    if ($pids) {
      foreach ($pids as $processId) {
        $cpu[$processId] = exec("ps -p ".$processId." -o %cpu");
        $sum = $sum + $cpu[$processId];
      }
    }
    if ($total) {
      return $sum;
    }
    return $cpu;
  }

  /**
   * Method to get the Memory usage of the processes in this queue
   *
   * @param array   $pids  The process ids. Optional. If empty, process IDs from queue will be used.
   * @param boolean $total Return one total number. Default false
   *
   * @return array with memory usage per process ID
   */
  public function getMemoryUsage($pids=array(), $total=false)
  {
    if (!$pids) {
      $pids = $this->getProcessPids();
    }
    $mem = array();
    $sum = 0;
    if ($pids) {
      foreach ($pids as $processId) {
        $mem[$processId] = exec("ps -p ".$processId." -o %mem");
        $sum = $sum + $mem[$processId];
      }
    }
    if ($total) {
      return $sum;
    }
    return $mem;
  }

  /**
   * Method to remove a process from this queue
   *
   * @param array $pids The process ids. Optional. If empty, process IDs from queue will be used.
   *
   * @return null
   */
  public function removeOneProcess($pids=array())
  {
    if (!$pids) {
      $pids = $this->getProcessPids();
    }
    $countPids = count($pids);
    if ($countPids) {
      posix_kill($pids[$countPids-1], 9);
    }
  }

  /**
   * Method to remove all process from this queue
   *
   * @param array $pids The process ids. Optional. If empty, process IDs from queue will be used.
   *
   * @return null
   */
  public function removeAllProcesses($pids=array())
  {
    if (!$pids) {
      $pids = $this->getProcessPids();
    }
    foreach ($pids as $pid) {
      posix_kill($pid, 9);
    }
  }

  /**
   * Method to add a process.
   *
   * @param integer $children The number of children to add to the process.
   *
   * @return null
   */
  public function addProcess($children=1)
  {
    $accountUrl = $this->getAccountUrl();
    $type = $this->getType();
    $path = '/var/www/'.$accountUrl.'/scripts/';
    $params = '{"queue":"'.$type.'", "count":"'.$children.'"}';
    exec($path.'script.php -s=Core_Resque_Worker -p='.$params);
  }

}