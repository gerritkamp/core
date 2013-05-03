<?php
/**
 * The core model
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
class Core_Model extends Zend_Db_Table_Abstract
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var read database adapter
   */
  protected $_readDb;

  /**
   * @var write database adapter
   */
  protected $_writeDb;

  /**
   * @var integer initialization time
   */
   protected $_time;

  /**
   * Constructor
   *
   * @return None
   */
  public function __construct()
  {
    $this->_logger   = Zend_Registry::get('logger');
    $readDb          = Zend_Registry::get('read_db');
    $writeDb         = Zend_Registry::get('write_db');
    $this->_readDb   = $readDb;
    $this->_writeDb  = $writeDb;
    $this->_time     = time();
    Zend_Db_Table_Abstract::setDefaultAdapter($readDb);
  }

  /**
   * Method to get the table definition, if it exists.
   *
   * @return array Table definition
   */
  public function getTableDefinition()
  {
    if (!empty($this->_tableFields)) {
      return $this->_tableFields;
    } else {
      return array();
    }
  }

  /**
   * Method to get the table name
   *
   * @return string with table name, or false if none found
   */
  public function getTableName()
  {
    if (!empty($this->_tableName)) {
      return $this->_tableName;
    } else {
      return null;
    }
  }

  /**
   * Method to get the name of the parent class, if it exists
   *
   * @return string Name of parent class, or null if not found
   */
  public function getParentTableName()
  {
    $parentProps = get_class_vars(get_parent_class($this));
    if (!empty($parentProps['_tableName'])) {
      return $parentProps['_tableName'];
    }
    return null;
  }

  /**
   * Method to get the table fieldNames
   *
   * @return array with table field names
   */
  public function getTableFieldNames()
  {
    $fieldNames = array();
    if (!empty($this->_tableFields)) {
      foreach ($this->_tableFields as $field) {
        $fieldNames[] = $field[0];
      }
    }
    return $fieldNames;
  }

  /**
   * Method to insert data. Also inserts data for parent classes.
   *
   * @param  array   $cleanData Filtered data (this insertion is being escaped)
   * @param  integer $createdBy User who created the entry
   *
   * @return array The inserted data
   */
  public function insertNewRecord($cleanData, $createdBy=0)
  {
    // only if class has a tablename
    $tableName = $this->getTableName();
    if ($tableName) {
      // first insert data into parent
      $parentName = $this->getParentTableName();
      if (!empty($parentName)) {
        $this->_logger->debug(__METHOD__.' parent name: '.$parentName);
        $parentClassName = get_parent_class($this);
        $parentClass = new $parentClassName();
        $parentData = $parentClass->insertNewRecord($cleanData, $createdBy);
      }

      // add some default fields
      if (!empty($parentData['id'])) {
        $cleanData['id'] = $parentData['id'];
      }
      if (empty($cleanData['cruser_id']) && $createdBy) {
        $cleanData['cruser_id'] = $createdBy;
      }
      $cleanData['crdate'] = $this->_time;

      // check with fields should be inserted
      $fieldNames = $this->getTableFieldNames();
      $insertData = array();
      foreach ($cleanData as $key => $value) {
        if (in_array($key, $fieldNames)) {
          $insertData[$key] = $value;
        }
      }

      // insert the data
      $saved = $this->_writeDb->insert($tableName, $insertData);
      if ($saved && empty($insertData['id'])) {
        $insertData['id'] = $this->_writeDb->lastInsertId();
      }
      if (!empty($parentData)) {
        return array_merge($parentData, $insertData);
      } else {
        return array_merge($insertData);
      }
    }

  }
}