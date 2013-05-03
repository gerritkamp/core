<?php

/**
 * Core_Validate_Number, filters everything except numbers
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
class Core_Validate_Numeric extends Zend_Validate_Abstract
{
  const NUMERIC = 'invalidNumberic';

  protected $_messageTemplates = array(
    self::NUMERIC => "'%value%' is not a fully numeric string"
  );

  public function isValid($value)
  {
    // perform some transformation upon $value to arrive at $number
    $allowedCharacters = array('0','1','2','3','4','5','6','7','8','9');

    // make sure we only have the characters listed above in our string
    $number = $value;
    for ($i=0; $i<strlen($value); $i++) {
      $char = $value[$i];
      if (!in_array($char, $allowedCharacters)) {
        $this->_error(self::NUMERIC);
        return false;
      }
    }
    return true;
  }

}