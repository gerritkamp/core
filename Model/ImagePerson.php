<?php
/**
 * The ImagePerson model
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
class Core_Model_ImagePerson extends Core_Model
{

  protected $_tableName = "core_image_person";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("crdate", "int(10) unsigned", "NO", "", "0", ""),
    array("cruser_id", "int(10) unsigned", "NO", "", "0", ""),
    array("deleted", "tinyint(3) unsigned", "NO", "", "0", ""),
    array("image_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("person_id", "int(10) unsigned", "NO", "MUL", "0", ""),
    array("is_default", "tinyint(3) unsigned", "NO", "", "0", "")
  );

  protected $_image;

  public function __construct()
  {
    parent::__construct();
    $this->_image = new Core_Model_Image();
  }

  /**
   * Insert a new image for a specific person
   *
   * @param  array   $imageData   The image data
   * @param  string  $storageType The storage type
   * @param  integer $userId      The user ID
   * @param  boolean $default     Is this a default?
   * @param  integer $createdBy   The user who created it
   *
   * @return Inserted data
   */
  public function saveNewImagePerson($imageData, $storageType, $userId, $default, $createdBy)
  {
    $this->_logger->info(__METHOD__);
    $imageData = $this->_image->insertNewRecord($imageData, $storageType, $createdBy);
    // now save image_user
    if (!empty($imageData['id'])) {
      $imageUserData['image_id']  = $imageData['id'];
      $imageUserData['crdate']    = $this->_time;
      $imageUserData['cruser_id'] = $createdBy;
      $imageUserData['is_default']   = $default ? 1 : 0;
      $imageUserData['person_id'] = $userId;
    }
    $this->_logger->debug(__METHOD__.' image user data: '.print_r($imageUserData, true));
    return $insertedData = $this->insertNewRecord($imageUserData);
  }

}