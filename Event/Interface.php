<?php
/**
 * Interface that all events should implement.
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
interface Core_Event_Interface
{

  /**
   * Method to process the event
   *
   * @param  integer $fromPersonId The person who caused the event
   * @param  array   $params       Submitted params
   *
   * @return mixed false upon error, array with params upon success
   */
  public function processEvent($fromPersonId=0, $params=array());

}