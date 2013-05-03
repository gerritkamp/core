<?php

/**
 * Script to generate models based on the database
 *
 * @category   Core
 * @package    Core_Scripts
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Scripts
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Scripts_GenerateObjects extends Core_Scripts
{

  public function executeScript()
  {
    $database = new Core_Database();
    $n = $database->generateObjectsFromTables();
    return $n.' Objects generated!';
  }
}