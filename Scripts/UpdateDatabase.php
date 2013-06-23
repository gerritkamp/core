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
class Core_Scripts_UpdateDatabase extends Core_Scripts
{

  public function executeScript($params)
  {
    $database = new Core_Database();
    $exec         = true;
    $reset        = false;
    $returnError  = true;
    $deleteTables = true;
    $result = $database->updateDatabase($exec, $reset, $returnError, $deleteTables);
    if ($result) {
      return ' Database updated!';
    } else {
      return 'Some error occured, check the logs!';
    }
  }
}