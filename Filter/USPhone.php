<?php

/**
 * Core_Filter_USPhone, manages the filtering of US phone number input
 *
 * @category   Core
 * @package    Core_Filter
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Filter
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Filter_USPhone implements Zend_Filter_Interface
{
  public function filter($value)
  {
    // perform some transformation upon $value to arrive at $phoneNumber
    $allowedCharacters = array(
      '0','1','2','3','4','5','6','7','8','9','+','-','(',')'
    );

    // remove leading and trailing spaces if present
    $value = trim($value);

    // make sure we only have the characters listed above in our string
    $phoneNumber = $value;
    for ($i=0; $i<strlen($value); $i++) {
      $char = $value[$i];
      if (!in_array($char, $allowedCharacters)) {
        $phoneNumber = str_replace($char, '', $phoneNumber);
      }
    }
    $phoneNumber = str_replace(')', ') ', $phoneNumber);
    return $phoneNumber;
  }

  /**
   * Method to render a US phone number integer as a readable phone number
   *
   * @param  integer $number The US phone number
   *
   * @return string The formatted US phone number
   */
  public function displayInteterAsUSPhoneNumber($number=0)
  {
    return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $number);
  }
}