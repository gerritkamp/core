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
class Core_Datatable
{
  protected $_logger;
  protected $_db;
  protected $_columns = array();
  protected $_columnFields = array();
  protected $_params = array();
  protected $_getOptions = array(
    'sEcho',
    'iColumns',
    'sColumns',
    'iDisplayStart',
    'iDisplayLength',
    'mDataProp_',
    'sSearch',
    'bRegex',
    'sSearch_',
    'bRegex_',
    'bSearchable_',
    'sSearch_',
    'bRegex_',
    'bSearchable_',
    'iSortCol_',
    'sSortDir_',
    'iSortingCols',
    'bSortable_',
  );
  protected $_jsConfig = '';


  public function __construct($options=array())
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_db     = Zend_Registry::get('read_db');
    if (!empty($options['params'])) {
      $this->_setGetParams($options['params']);
    }
  }

  /**
   * Method to generate the HTML for the table
   *
   * @return string The HTML
   */
  public function generateTableHtml()
  {
    $content = '
      <table id="datatable" class="table table-striped table-bordered dTableR">
          <thead><tr>';
    foreach ($this->_columns as $label => $title) {
      $content.='<th>'.$title.'</th>';
    }
    $content.='</tr></thead><tbody><tr>
             <td class="dataTables_empty" colspan="'.count($this->_columns).'">Loading data from server</td>
              </tr></tbody>
      </table>
      <script type="text/javascript">'.$this->_jsConfig.'</script>';
    return $content;
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
    $from = $this->_getFrom();
    if (empty($this->_params['sSearch'])) {
      $from[1][0] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS '.$from[1][0]);
    }
    $select->from($from[0], $from[1]);

    // get where
    // @todo add search option
    // @todo add option to use SQL_CALC_FOUND_ROWS
    // $todo make phone numbers integers and put them in a separate table (with type, extension)

    // get group
    $select->group($this->_getGroupBy());

    // get order by
    for ($i=0; $i < $this->_params['iSortingCols']; $i++) {
      $field = $fields[$this->_params['iSortCol_'.$i]];
      if (isset($this->_columnFields[$field])) {
        $field = $this->_columnFields[$field];
      }
      $sort[] = $field.' '.$this->_params['sSortDir_'.$i];
    }
    $select->order($sort);

    // get total rows
    if (empty($this->_params['sSearch'])) {
      // get total number of records
      $query = "SELECT FOUND_ROWS();";
      $countRows = $this->_db->query($query)->fetch();
    } else {

    }

    // add pagination and get data
    $select->limit($this->_params['iDisplayLength'], $this->_params['iDisplayStart']);
    $this->_logger->debug(__METHOD__.' query: '.$select->__toString());
    $data = $select->query()->fetchAll();

    // get formattted data and ensure we only return configured columns
    foreach ($data as $key => $row) {
      // get formatted data
      $row = $this->_formatRow($row);
      // ensure we got all columsn and not one more
      $rec = array();
      foreach ($fields as $index => $label) {
        $rec[$index] = $row[$label];
      }
      $aaData[] = $rec;
    }

    $this->_logger->debug(__METHOD__.' aaData: '.print_r($aaData, true));

    return array(
      "sEcho" => $this->_params['sEcho'],
      "iTotalRecords" => 10,
      "iTotalDisplayRecords" => count($aaData),
      "aaData" => $aaData
    );
  }



  /**
   * Method to process all the get params and filter them
   *
   * @param array $params Request params
   */
  protected function _setGetParams($params)
  {
    //$this->_logger->debug(__METHOD__.' params: '.print_r($params, true));
    foreach ($params as $key => $value) {
      foreach ($this->_getOptions as $option) {
        $optionLength = strlen($option);
        if (in_array((substr($key, 0, $optionLength)), $this->_getOptions)) {
          $this->_params[$key] = $value;
        }
      }
    }
    $this->_logger->debug(__METHOD__.' params: '.print_r($this->_params, true));
  }

  // options: 1) with or 2) without search
  // 1. If no search, options: a) use SQL_CALC, or b) use two queries
  // 2. If search, run count() first to get total and then a) use SQL_CALC, or b) use two queries

  /**
   * Method to get the where statement. Must be overridden in the inhering classes.
   *
   * @return array with zero or more where statements
   */
  protected function _getWhere()
  {
  }

  /**
   * Method to get the group by statement. Must be overridden in the inhering classes.
   *
   * @return string or array of columns to be used for grouping
   */
  protected function _getGroupBy()
  {
  }

  /**
   * Method to process (format) an individual row. Must be overridden in the inhering classes.
   *
   * @return array with zero or more where statements
   */
  protected function _formatRow()
  {
  }

}