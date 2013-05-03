<?php
/**
 * The email model
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
class Core_Model_EmailPerson extends Core_Model
{
  protected $_tableName = "core_email_person";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("email", "varchar(255)", "NO", "UNI", "", ""),
    array("person_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("default", "tinyint(3) unsigned", "NO", "", "0", "")
  );

  /**
   * Method to save a new email person
   *
   * @param string  $email     The email address
   * @param integer $userId    The user ID who'se email this is
   * @param boolean $default   Optional, default email?
   * @param integer $createdBy Optional, created by
   *
   * @return boolean true upon success, false otherwise
   */
  public function saveNewEmailPerson($email, $userId, $default=false, $createdBy=0)
  {
    $this->_logger->info(__METHOD__);
    if (empty($email)) {
      $this->_logger->err(__METHOD__.' no email given.');
      return false;
    }
    // verify that email is unique
    $existingEmail = $this->getEmailDetails($email);
    if (!empty($existingEmail)) {
      $this->_logger->err(__METHOD__.' email: '.$email.' is not unique!');
      return false;
    }
    // now save email_person
    $emailPersonData['email']     = $email;
    $emailPersonData['crdate']    = $this->_time;
    $emailPersonData['cruser_id'] = $createdBy;
    $emailPersonData['default']   = $default ? 1 : 0;
    $emailPersonData['person_id'] = $userId;
    $this->_logger->debug(__METHOD__.' emailpersondata: '.print_r($emailPersonData, true));
    $saved = $this->_writeDb->insert($this->_tableName, $emailPersonData);
    if (!$saved) {
      $this->_logger->err(__METHOD__.' could not save email user with data: '.print_r($emailPersonData, true));
      return false;
    }
    return true;
  }

  /**
   * Method to get the email details
   *
   * @param  string $email An email
   *
   * @return mixed array with email and user details, if found, false otherwise
   */
  public function getEmailDetails($email)
  {
    $this->_logger->info(__METHOD__);
    $db = $this->_readDb;
    $select = $db->select()
       ->from(array('ep' => $this->_tableName))
       ->joinLeft(array('p' => 'core_person'),
          'p.id = ep.person_id')
       ->where('ep.email = ?', $email);
    $results = $db->query($select)->fetchAll();
    $this->_logger->debug(__METHOD__.' results: '.print_r($results, true));
    if (!empty($results)) {
      return $results;
    } else {
      return false;
    }
  }

}