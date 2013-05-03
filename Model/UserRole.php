<?php
/**
 * The UserRole model
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
class Core_Model_UserRole extends Core_Model
{

  protected $_tableName = "core_user_role";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("user_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("role", "int(10) unsigned", "NO", "MUL", "0", "")
  );

}