<?php
/**
 * The phonenumber model
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
class Core_Model_Phonenumber extends Core_Model
{
  protected $_tableName = "core_phonenumber";

  protected $_phonenumberPerson;

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("number", "varchar(15)", "NO", "MUL", "", "")
  );

  public function __construct()
  {
    parent::__construct();
    $this->_phonenumberPerson = new Core_Model_PhonenumberPerson();
  }

  /**
   * Method to get the phone number details
   *
   * @param  string $number A phone number
   *
   * @return mixed array with phone and user details, if found, false otherwise
   */
  public function getPhoneNumberDetails($number)
  {
    $this->_logger->info(__METHOD__);
    $db = $this->_readDb;
    $select = $db->select()
       ->from(array('n' => $this->_tableName))
       ->join(array('pp' => 'core_phonenumber_person'),
          'pp.phonenumber_id = n.id')
       ->joinLeft(array('p' => 'core_person'),
          'p.id = pp.person_id')
       ->where('n.number = ?', $number);
    $results = $db->query($select)->fetchAll();
    $this->_logger->debug(__METHOD__.' results: '.print_r($results, true));
    if (!empty($results)) {
      return $results;
    } else {
      return false;
    }
  }

  /**
   * Method to get the phone number ID by its number
   *
   * @param  string $number A phone number
   *
   * @return mixed array with phone and user details, if found, false otherwise
   */
  public function getPhoneNumberDataByNumber($number)
  {
    $this->_logger->info(__METHOD__);
    $db = $this->_readDb;
    $select = $db->select()
       ->from(array('n' => $this->_tableName))
       ->where('n.number = ?', $number);
    $results = $db->query($select)->fetchAll();
    $this->_logger->debug(__METHOD__.' results: '.print_r($results, true));
    if (!empty($results[0]) && is_array($results)) {
      return $results[0];
    } else {
      return false;
    }
  }

}