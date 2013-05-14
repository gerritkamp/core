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
       ->from(array('u' => 'core_user'))
       ->joinLeft(array('p' => 'core_person'),
          'u.id = p.id')
       ->joinLeft(array('ep' => 'core_email_person'),
          'u.id = ep.person_id')
       ->where('u.id = ?', $userId)
       ->where('ep.default = 1');
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

  /**
   * Method to get a user by an email address
   *
   * @param  string  $email           The email address
   * @param  boolean $includePassword Include sensitive data such as passwords?
   *
   * @return mixed array with userdata or false if none found
   */
  public function getUserByEmail($email, $includePassword=false)
  {
    $this->_logger->info(__METHOD__);
    $db = $this->_readDb;
    $select = $db->select()
       ->from(array('ep' => 'core_email_person'))
       ->joinLeft(array('u' => 'core_user'),
          'u.id = ep.person_id')
       ->where('ep.email = ?', $email);
    $results = $db->query($select)->fetchAll();
    if (!empty($results[0]['person_id'])) {
      // filter out sensitive data if need be
      if (!$includePassword) {
        unset($results[0]['password']);
        unset($results[0]['usersalt']);
      }
      return $results[0];
    } else {
      return false;
    }
  }

  /**
   * Method to get basic user details through the user token
   *
   * @param  string $token The user token
   *
   * @return array with user data (incl password + salt!) or false if none found
   */
  public function getUserByToken($token)
  {
    $this->_logger->info(__METHOD__);
    $select = $this->_readDb->select()
       ->from(array('u' => 'core_user'))
       ->where('u.token = ?', $token);
    $results = $this->_readDb->query($select)->fetchAll();
    if (isset($results[0]['id'])) {
      return $results[0];
    } else {
      return false;
    }
  }

  /**
   * Method to reset the user token
   *
   * @param  integer $userId The user ID
   *
   * @return string The user token, or false upon error
   */
  public function resetToken($userId)
  {
    $this->_logger->info(__METHOD__);
    $userId = (int)$userId;
    $time = time();
    $random = mt_rand(0, 999999);
    $token = sha1($time.$random.$userId);
    $db = $this->_writeDb;
    $data = array('token' => $token);
    $n = $db->update($this->_tableName, $data, 'id='.$userId);
    if ($n) {
      return $token;
    } else {
      $this->_logger->warn(__METHOD__.' failed to reset token for user with id: '.$userId);
      return false;
    }
  }

}