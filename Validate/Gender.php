<?php

/**
 * Core_Filter_Validate, manages the validation of a gender string
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
class Core_Validate_Gender extends Zend_Validate_Abstract
{
  const GENDER = 'invalidGender';

  protected $_messageTemplates = array(
    self::GENDER => "'%value%' is not a valid gender"
  );

  public function isValid($value)
  {
    $this->_setValue($value);

    $validGenders = array(
      'm',
      'f',
      'male',
      'female'
    );
    if (!in_array($value, $validGenders)) {
      $this->_error(self::GENDER);
      return false;
    }

    return true;
  }
}