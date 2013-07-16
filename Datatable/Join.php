<?php
/**
 * The datatable manager for tables with multiple sources
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

/**
 * Class to make it possible to use two datasources for one datatable.
 * Should be extended by specific library classes
 */
class Core_Datatable_Join
{

  protected $_sources;
  protected $_logger;
  protected $_columns = array();
  protected $_columnFields = array();
  protected $_searchFields = array();
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
    if (!empty($options['params'])) {
      $this->_setGetParams($options['params']);
    }
    $this->_columns = $this->getColumns();
    $this->_columnFields = $this->getColumnFields();
    $this->_searchFields = $this->getSearchFields();
    $this->_jsConfig = $this->getJsConfig();
  }

  /**
   * Method to get the data.
   *
   * return array The retrieved data
   */
  public function getData()
  {
    $this->_logger->info(__METHOD__);
/*    return array(
      "sEcho" => $this->_params['sEcho'],
      "iTotalRecords" => $countRows,
      "iTotalDisplayRecords" => $countDisplayRows,
      "aaData" => $aaData
    );*/
    // is there search
      // is it from multiple columns
      return $this->_getCombinedSearch();
      // else
      return $this->_getSingleSearch();
    // else
    return $this->_getPagedData();
  }

  protected function _getCombinedSearch()
  {
    $this->_logger->info(__METHOD__);
  }

  protected function _getSingleSearch()
  {
    $this->_logger->info(__METHOD__);
  }

  protected function _getPagedData()
  {
    $this->_logger->info(__METHOD__);
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
    //$this->_logger->debug(__METHOD__.' params: '.print_r($this->_params, true));
  }

}