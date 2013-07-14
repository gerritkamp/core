<?php
/**
 * The Event model
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
    array("person_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("event_type_id", "int(10) unsigned", "NO", "", "0", ""),
    array("params", "text", "YES", "", "", "")
  );

}