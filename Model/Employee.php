<?php
/**
 * The employee model
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
class Core_Model_Employee extends Core_Model_Person
{
  protected $_tableName = "core_employee";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("social", "varchar(20)", "YES", "", "", "")
  );

  /**
   * Method to save a new employee
   *
   * @param  array   $cleanData Employee data that needs to be saved
   * @param  integer $createdBy User who inserted this employee
   *
   * @return mixed Array with employee data upon success, boolean false on error
   */
  public function saveNewEmployee($cleanData, $createdBy=0)
  {
    $this->_logger->info(__METHOD__);
    // save new person
    $personId = $this->saveNewPerson($cleanData, $createdBy);
    if ($personId) {
      // save the employee
      $employeeData['person_id']        = $personId;
      $employeeData['crdate']    = $this->_time;
      $employeeData['cruser_id'] = $createdBy;
      foreach ($cleanData as $key => $value) {
        if (in_array($key, $this->_employeeFields)) {
          $employeeData[$key] = $value;
        }
      }
      $this->_logger->debug(__METHOD__.' employeedata: '.print_r($employeeData, true));
      $saved = $this->_writeDb->insert($this->_name, $employeeData);
      if (!$saved) {
        $this->_logger->err(__METHOD__.' could not save employee with data: '.print_r($cleanData, true));
        return false;
      } else {
        $this->_logger->err(__METHOD__.' could not save person with data: '.print_r($cleanData, true));
        return false;
      }
      return $employeeData;
    }
  }
}