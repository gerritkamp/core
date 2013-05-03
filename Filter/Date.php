<?php

/**
 * Core_Filter_Date, manages the filtering of simple date input
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
class Core_Filter_Date implements Zend_Filter_Interface
{

  /**
   * Filter function
   *
   * @param string $value Unclean input
   *
   * @return string       Filtered string
   */
  public function filter($value)
  {
    // perform some transformation upon $value to arrive on a date

    // remove leading and trailing spaces and tabs and new lines etc
    $value = trim($value);

    // strip tags
    $value = strip_tags($value);

    // try to transform into a date string
    try {
      $date = new DateTime($value);
      $value = $date->format('m/d/Y');
    } catch (Exception $e) {
      // do nothing
    }

    return $value;
  }
}