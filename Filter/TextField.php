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
class Core_Filter_TextField implements Zend_Filter_Interface
{

  /**
   * Filter function
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

    return $string;
  }
}