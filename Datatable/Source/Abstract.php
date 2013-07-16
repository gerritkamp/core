<?php

/**
 * Abstract event
 *
 * @category   Core
 * @package    Core_Datatable
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Datatable
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
abstract class Core_Datatable_Source_Abstract
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var Logger
   */
  protected $_session;

  /**
   * @var String event type
   */
  protected $_type;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_session = Zend_Registry::get('session');
  }

  /**
   * Get ids and the data needed for sorting/paging for certain search term
   *
   * @return mixed
   */
  abstract protected function getSortedSearchData($params=array());

  /**
   * Get data given a set of ids
   *
   * @return mixed
   */
  abstract protected function getDataByIds($ids=array());

  /**
   * Get data and ids given a sort column and paging params
   *
   * @return mixed
   */
  abstract protected function getData($ids=array());

}