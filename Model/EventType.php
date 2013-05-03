<?php
/**
 * The EventType model
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
class Core_Model_EventType extends Core_Model
{

  protected $_tableName = "core_event_type";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("name", "varchar(255)", "NO", "", "", ""),
    array("kpi", "int(3) unsigned", "NO", "", "0", ""),
    array("email", "int(3) unsigned", "NO", "", "0", ""),
    array("internal", "int(3) unsigned", "NO", "", "0", ""),
    array("audit", "int(3) unsigned", "NO", "", "0", "")
  );

}