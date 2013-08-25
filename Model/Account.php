<?php
/**
 * The Account model
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
class Core_Model_Account extends Core_Model
{

  protected $_tableName = "core_account";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("name", "varchar(255)", "NO", "", "", ""),
    array("organization_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("account_status", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("public_key", "varchar(80)", "NO", "", "", ""),
    array("secret_key", "varchar(80)", "NO", "", "", ""),
  );

}