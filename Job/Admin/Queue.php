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

  public function setAccountUrl($accountUrl)
  {
    $this->_accountUrl = $accountUrl;
  }

  public function setType($type)
  {
    $this->_type = $type;
  }

  public function getQueueName()
  {
    if (!empty($this->_accountUrl) && !empty($this->_type)) {
      return $this->_accountUrl.':'.$this->_type;
    }
    return null;
  }

  public function getWaitingCount()
  {
      return $this->_redis->llen($this->_prefix.':queue:'.$this->getQueueName());
  }

  public function getFailedCount()
  {
    return $this->_redis->get($this->_prefix.':stat:failed:'.$this->getQueueName());
  }

  public function getProcessedCount()
  {
    return $this->_redis->get($this->_prefix.':stat:processed:'.$this->getQueueName());
  }

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



}