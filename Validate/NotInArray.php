<?php

/**
 * Core_Validate_NotInArray, verifies something is NOT in an array
 *
 * @category   Core
 * @package    Core_Validate
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Validate
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Validate_NotInArray extends Zend_Validate_Abstract
{
  const NOTINARRAY = 'invalidNotInArray';

  protected $_messageTemplates = array(
    self::NOTINARRAY => "'%value%' is not allowed"
  );

  /**
   * @var Array with items that are not allwed
   */
  protected $_notAllowedArray;

  /**
   * Sets default option values for this instance
   *
   * @param  array $array
   * @return void
   */
  public function __construct($notAllowedArray)
  {
      $this->_notAllowedArray = (array) $notAllowedArray;
  }

  public function isValid($value)
  {
    $this->_setValue($value);

    if (in_array($value, $this->_notAllowedArray)) {
      $this->_error(self::NOTINARRAY);
      return false;
    }

    return true;
  }
}