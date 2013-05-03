<?php
/**
 * The user model
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
class Core_Model_User extends Core_Model_Person
{
  protected $_tableName = "core_user";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("userstatus_id", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("password", "varchar(255)", "NO", "", "", ""),
    array("usersalt", "varchar(255)", "NO", "", "", ""),
    array("token", "varchar(255)", "NO", "", "", "")
  );



  /**
   * Method to get a user by its ID
   *
   * @param  integer $userId          The user ID
   * @param  boolean $includePassword Include sensitive data such as passwords?
   *
   * @return mixed array with userdata or false if none found
   */
  public function getUserById($userId, $includePassword=false)
  {
    $this->_logger->info(__METHOD__);
    $db = $this->_readDb;
    $select = $db->select()
       ->from($this->_userTable)
       ->where('person_id = ?', $userId);
    $results = $db->query($select)->fetchAll();
    $this->_logger->debug(__METHOD__.' results: '.print_r($results, true));
    if (!empty($results[0]['person_id'])) {
      // filter out sensitive data if need be
      if (!$includePassword) {
        unset($results[0]['password']);
        unset($results[0]['usersalt']);
      }
      // add image path and thumb path if applicable
      $image = new Core_Image();
      if (!empty($results[0]['image'])) {
        $results[0]['image_path'] = $image->getImagePath().$results[0]['image'];
      }
      if (!empty($results[0]['thumb'])) {
        $results[0]['thumb_path'] = $image->getImagePath().$results[0]['thumb'];
      }
      return $results[0];
    } else {
      return false;
    }
  }

  public function getUserByEmail($email)
  {
    $this->_logger->info(__METHOD__);
   /*$select = $this->_readDb->select()
      ->from('core_')*/
  }
}