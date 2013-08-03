<?php

/**
 * Core_Filter_TextField, manages the filtering of simple text inputs
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
class Core_Filter_UrlPart implements Zend_Filter_Interface
{

  /**
   * Filter function, to turn something into an alphanumeric, lowercase string.
   *
   * @param string $value Unclean input
   *
   * @return string        Filtered string
   */
  public function filter($value)
  {
    // perform some transformation upon $value to arrive on $sting

    // remove leading and trailing spaces and tabs and new lines etc
    $value = trim($value);

    // strip tags
    $string = strip_tags($value);

    // only alphanumeric
    $alNum = new Zend_Filter_Alnum();
    $string = $alNum->filter($string);

    // lowercase
    $string = strtolower($string);

    return $string;
  }
}