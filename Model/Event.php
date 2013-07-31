<?php
/**
 * The Event model.
 *
 * Status:
 * 0 = unprocessed
 * 1 = in process
 * 2 = sucessfully processed
 * 3 = failed
 *
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Model_Event extends Core_Model
{

  protected $_tableName = "core_event";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("tstamp", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("person_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("event_type_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("status_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("params", "text", "YES", "", "", "")
  );

  /**
   * Method to update an event status
   *
   * @param integer $id     The event ID
   * @param integer $status The event status
   *
   * @return null
   */
  public function updateStatus($id, $status)
  {
    $this->_logger->info(__METHOD__);
    $this->_logger->info(__METHOD__.'. id: '.$id);
    if (is_numeric($status)) {
      $params = array(
        'tstamp' => time(),
        'status_id' => $status
      );
      $this->updateRecord($id, $params);
    }
  }
}