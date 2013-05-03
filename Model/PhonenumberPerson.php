<?php
/**
 * The PhonenumberPerson model
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
class Core_Model_PhonenumberPerson extends Core_Model
{

  protected $_tableName = "core_phonenumber_person";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("phonenumber_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("phonenumber_type", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("person_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("default", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("extension", "int(10) unsigned", "NO", "", "0", "")
  );

  protected $_phonenumberTypes = array(
    'mobile' => 1,
    'home'   => 2,
    'work'   => 3,
    'fax'    => 4,
  );

  /**
   * Method to save a new phonenumber for a person
   *
   * @param array   $cleanData The data
   * @param integer $createdBy Optional, the user who created this record
   *
   * @return array The inserted data
   */
  public function saveNewPhonenumberPerson($cleanData, $createdBy=0)
  {
    $this->_logger->info(__METHOD__);
    if (empty($cleanData['number'])) {
      $this->_logger->err(__METHOD__.' no number given.');
      return false;
    }
    $number = $cleanData['number'];
    // check if number already exists
    $phoneNumber = new Core_Model_Phonenumber();
    $existingPhonenumber = $phoneNumber->getPhoneNumberDataByNumber($number);
    if (!empty($existingPhonenumber['id'])) {
      $cleanData['phonenumber_id'] = $existingPhonenumber['id'];
    } else {
      $numberData = array('number' => $number);
      $phonenumberData = $phoneNumber->insertNewRecord($numberData, $createdBy);
      $cleanData['phonenumber_id'] = $phonenumberData['id'];
    }
    if (!empty($cleanData['type']) && array_key_exists($cleanData['type'], $this->_phonenumberTypes)) {
      $cleanData['phonenumber_type'] = $this->_phonenumberTypes[$cleanData['type']];
    }
    $insertedData = $this->insertNewRecord($cleanData, $createdBy);
    return $insertedData;
  }

}