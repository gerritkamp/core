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
   * @var String event type
   */
  protected $_columns;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_session = Zend_Registry::get('session');
  }

  /**
   * Get count of all records before applying any search filter
   *
   * @return mixed
   */
  abstract protected function getIdsBySearchData($params);

  /**
   * Get data given a set of ids
   *
   * @return mixed
   */
  abstract protected function getDataByIds($ids);

  /**
   * Get data and ids given a sort column and paging params
   *
   * @param array $params The get params and the sort_column
   * @param array $ids    Optional. If given, results are part of this set
   *
   * @return mixed
   */
  abstract protected function getPagedData($params, $ids=array());

  /**
   * Method to return the columns
   *
   * @return array The columns
   */
  public function getColumns()
  {
    return $this->_columns;
  }

}