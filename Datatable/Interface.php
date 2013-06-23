<?php
/**
 * Interface that all events should implement.
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
interface Core_Datatable_Interface
{

  /**
   * Method to get the columns that need to be returned
   */
  public function getColumns();

  /**
   * Method to get the real fields for certain columns (like 'edit' => 'id')
   */
  public function getColumnFields();

  /**
   * Method to get the fields that are searchable
   */
  public function getSearchFields();

  /**
   * Method to get the javascript configuration for the datatable
   */
  public function getJsConfig();

  /**
   * Method to get the from statements
   */
  public function getFrom();

  /**
   * Method to get the join statements
   */
  public function getJoin();

  /**
   * Method to get the where statements
   */
  public function getWhere();

  /**
   * Method to get the groupby statements
   */
  public function getGroupBy();

  /**
   * Method to format each row
   */
  public function formatRow($record);

}