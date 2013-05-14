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

  /**
   * Method to get the roles for a given user
   *
   * @param  integer $userId The user ID
   *
   * @return array roleIds
   */
  public function getUserRoles($userId)
  {
    $this->_logger->info(__METHOD__);
    $select = $this->_readDb->select()
       ->from(array('ur' => 'core_user_role'))
       ->where('ur.user_id = ?', $userId);
    $results = $this->_readDb->query($select)->fetchAll();
    $roles = array();
    if ($results) {
      foreach ($results as $role) {
        $roles[] = $role['role'];
      }
    }
    return $roles;
  }
}