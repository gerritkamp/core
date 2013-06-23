<?php

/**
 * Event, manages factoring of events events
 *
 * @category   Core
 * @package    Core_Event
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Event
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
abstract class Core_Event
{

  /**
   * Constructor
   *
   * @param $type The event type
   */
  public function make($type, $library='Core')
  {
    $logger = Zend_Registry::get('logger');
    $logger->info(__METHOD__);
    try {
      // create the event class
      $aName = explode('_', $type);
      $className = $library.'_Event_';
      foreach ($aName as $part) {
        $className.= ucfirst($part);
      }
      $class = new $className();
      return $class;
    } catch (Exception $e) {
      $logger->err(__METHOD__.' error: '.$e->getMessage());
      return false;
    }
  }

}