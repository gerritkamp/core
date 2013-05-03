<?php
/**
 * The person model
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
class Core_Model_Person extends Core_Model
{
  protected $_tableName = "core_person";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("first_name", "varchar(50)", "NO", "", "", ""),
    array("last_name", "varchar(50)", "NO", "", "", ""),
    array("title", "varchar(10)", "YES", "", "", ""),
    array("gender", "varchar(8)", "YES", "", "", ""),
    array("date_of_birth", "int(11)", "NO", "", "0", "")
  );


}