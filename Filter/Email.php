<?php

/**
 * Core_Filter_Email, manages the filtering of email input
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
class Core_Filter_Email implements Zend_Filter_Interface
{
  public function filter($value)
  {
    // perform some transformation upon $value to arrive on $email

    // remove leading and trailing spaces if present
    $value = trim($value);

    // strip tags
    $value = strip_tags($value);

    // lowercase
    $value = strtolower($value);

    // remove spaces
    $email = str_replace(' ', '', $value);

    return $email;
  }
}