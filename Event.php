<?php

/**
 * Event, manages events
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
class Core_Event
{

  /**
   * Event types defined in core. To be overriden by applications.
   * @var array
   */
  protected $_eventTypes = array();

  /**
   * Constructor
   */
  public function __construct()
  {

  }

  public function storeEvent($params)
  {
    // stores event in db
  }

  public function storeKpi()
  {
    // stores kpi
  }

  public function sendEmail()
  {
    // sends email
  }

  public function storesInternalNotification()
  {

  }

  public function storeAuditTrail()
  {

  }
}