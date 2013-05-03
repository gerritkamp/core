<?php

/**
 * Core_Filter_Number, filters everything except numbers
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
class Core_Filter_Numbers implements Zend_Filter_Interface
{
  public function filter($value)
  {
    // perform some transformation upon $value to arrive at $number
    $allowedCharacters = array('0','1','2','3','4','5','6','7','8','9');

    // remove leading and trailing spaces if present
    $value = trim($value);

    // make sure we only have the characters listed above in our string
    $number = $value;
    for ($i=0; $i<strlen($value); $i++) {
      $char = $value[$i];
      if (!in_array($char, $allowedCharacters)) {
        $number = str_replace($char, '', $number);
      }
    }
    return $number;
  }

}