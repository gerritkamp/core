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
interface Core_Datatable_Join_Interface
{

  /**
   * Get count of all records before applying any search filter
   *
   * @return mixed
   */
  public function getCountAll();

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
   * Method to format each row
   */
  public function formatRow($record);

}