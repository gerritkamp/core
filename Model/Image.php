<?php
/**
 * The image model
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
class Core_Model_Image extends Core_Model
{
  protected $_tableName = "core_image";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("width", "int(6) unsigned", "NO", "", "0", ""),
    array("height", "int(6) unsigned", "NO", "", "0", ""),
    array("mime_type", "varchar(64)", "YES", "", "", ""),
    array("name", "varchar(128)", "YES", "", "", ""),
    array("path", "varchar(128)", "YES", "", "", ""),
    array("image_storage_type", "tinyint(3) unsigned", "NO", "MUL", "0", "")
  );

  protected $_imageStorageTypes = array(
    'local'      => 1,
    'amazon'     => 2,
    'rackspace'  => 3,
  );

  /**
   * Method to save a new image
   *
   * @param array   $cleanData   The clean image data
   * @param mixed   $storageType The  image type (either integer or string)
   * @param integer $createdBy   Optional, created by
   *
   * @return boolean true upon success, false otherwise
   */
  public function saveNewImage($cleanData, $storageType, $createdBy=0)
  {
    $this->_logger->info(__METHOD__);
    // save image
    $imageData['crdate'] = $this->_time;
    $imageData['cruser_id'] = $createdBy;
    if (is_int($storageType)) {
      $imageData['image_storage_type'] = $storageType;
    } elseif (!empty($this->_imageStorageTypes[$storageType])) {
      $imageData['image_storage_type'] = $this->_imageStorageTypes[$storageType];
    } else {
      $this->_logger->err(__METHOD__.' storage type: '.print_r($storageType, true).' could not be found');
      return false;
    }
    foreach ($cleanData as $key => $value) {
      if (in_array($key, $this->_imageFields)) {
        $imageData[$key] = $value;
      }
    }
    return $this->insertNewRecord($imageData);
  }

}