<?php
/**
 * The datatable manager
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
/*

make it optional to choose between two methods of getting totals
If you have a complex WHERE statement – SQL_CALC_FOUND_ROWS would be better.
But if you have a query with a simple WHERE with indexed columns –
two queries would be faster.
The basic rule is – if you have “Temporary table” in your
EXPLAIN – SQL_CALC_FOUND_ROWS will work fine.
*/
class Core_Datatable_Sql extends Core_Datatable
{
  public function __construct($options=array())
  {
    $this->_db     = Zend_Registry::get('read_db');
    parent::__construct($options);
    $this->_columnFields = $this->getColumnFields();
    $this->_searchFields = $this->getSearchFields();
  }

  /**
   * Method to get the data.
   *
   * return array The retrieved data
   */
  public function getData()
  {
    $fields = array_keys($this->_columns);
    // if no search, use Calc_Found
    $includeCalcFound = empty($this->_params['sSearch']) ? true : false;
    $select = $this->_db->select();
    $countSelect = $this->_db->select();  // used for search, to count all records
    $from = $this->getFrom();
    if (empty($this->_params['sSearch'])) {
      // prepend the SQL_CALC_FOUND_ROWS to the first field
      $from['select'][0] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS '.$from['select'][0]);
      $select->from($from['from'], $from['select']);
    }

    // any joins?
    $join = $this->getJoin();
    if ($join) {
      foreach ($join as $value) {
        $select->$value['type']($value['join'], $value['on'], $value['select']);
        $countSelect->$value['type']($value['join'], $value['on'], $value['select']);
      }
    }

    // get where
    $where = $this->getWhere();
    if ($where) {
      foreach ($where as $value) {
        $select->where($value['query'], $value['value']);
        $countSelect->where($value['query'], $value['value']);
      }
    }

    // search?
    if (!empty($this->_params['sSearch'])) {
      // get a count of the total dataset
      $countSelect->from($from['from'], 'COUNT(*)');
      $this->_logger->debug(__METHOD__.' query: '.$countSelect->__toString());
      $data = $countSelect->query()->fetchAll();
      $this->_logger->debug(__METHOD__.' initial data: '.print_r($data, true));
      if (!empty($data[0]['COUNT(*)'])) {
        $countRows = $data[0]['COUNT(*)'];
      }
      // then get the SQL_CALC_FOUND_ROWS for the filtered dataset
      $from['select'][0] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS '.$from['select'][0]);
      $select->from($from['from'], $from['select']);
      // cannot do simple orWhere because of wrong grouping
      // http://stackoverflow.com/questions/1179279
      $or = '';
      foreach ($this->_searchFields as $key => $field) {
        if ($key == 0) {
          $or.= $this->_db->quoteInto($field.' LIKE ?', '%'.$this->_params['sSearch'].'%');
        } else {
          $or.= ' OR '.$this->_db->quoteInto($field.' LIKE ?', '%'.$this->_params['sSearch'].'%');
        }
      }
      $select->where($or);
    }

    // add group
    $select->group($this->getGroupBy());

    // get order by
    for ($i=0; $i < $this->_params['iSortingCols']; $i++) {
      $field = $fields[$this->_params['iSortCol_'.$i]];
      if (isset($this->_columnFields[$field])) {
        $field = $this->_columnFields[$field];
      }
      $sort[] = $field.' '.$this->_params['sSortDir_'.$i];
    }
    $select->order($sort);

    // add pagination
    $select->limit($this->_params['iDisplayLength'], $this->_params['iDisplayStart']);

    // get data
    $this->_logger->debug(__METHOD__.' query: '.$select->__toString());
    $data = $select->query()->fetchAll();

    // get number of filtered records
    $query = "SELECT FOUND_ROWS();";
    $displayRows = $this->_db->query($query)->fetch();
    if (!empty($displayRows['FOUND_ROWS()'])) {
      $countDisplayRows = $displayRows['FOUND_ROWS()'];
    }
    // if no search, display(filtered) and total are the same
    if (empty($this->_params['sSearch'])) {
      $countRows = $countDisplayRows;
    }

    // get formattted data and ensure we only return configured columns
    foreach ($data as $key => $row) {
      // get formatted data
      $row = $this->formatRow($row);
      // ensure we got all columns and not one more
      $rec = array();
      foreach ($fields as $index => $label) {
        $rec[$index] = $row[$label];
      }
      $aaData[] = $rec;
    }

    //$this->_logger->debug(__METHOD__.' aaData: '.print_r($aaData, true));

    return array(
      "sEcho" => $this->_params['sEcho'],
      "iTotalRecords" => $countRows,
      "iTotalDisplayRecords" => $countDisplayRows,
      "aaData" => $aaData
    );
  }

  // options: 1) with or 2) without search
  // 1. If no search, options: a) use SQL_CALC, or b) use two queries
  // 2. If search, run count() first to get total and then a) use SQL_CALC, or b) use two queries


}