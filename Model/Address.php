<?php
/**
 * The Address model
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
class Core_Model_Address extends Core_Model
{

  protected $_tableName = "core_address";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("address_1", "varchar(255)", "NO", "", "", ""),
    array("address_2", "varchar(255)", "NO", "", "", ""),
    array("city", "varchar(255)", "NO", "", "", ""),
    array("zip", "varchar(20)", "NO", "", "", ""),
    array("country_zone_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("longitude", "varchar(20)", "NO", "", "", ""),
    array("latitude", "varchar(20)", "NO", "", "", "")
  );

}