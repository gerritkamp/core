<?php
/**
 * The ImageUser model
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
class Core_Model_ImageUser extends Core_Model
{

  protected $_tableName = "core_image_user";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("image_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("user_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("default", "tinyint(3) unsigned", "NO", "", "0", "")
  );

}