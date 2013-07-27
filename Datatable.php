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

abstract class Core_Datatable
{
  protected $_logger;
  protected $_db;
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
    $this->_columns = $this->getColumns();
    if (!empty($options['params'])) {
      $this->_setGetParams($options['params']);
    }
    if (empty($options['ignoreJsConfig'])) {
      $this->_jsConfig = $this->getJsConfig();
    }
  }

  /**
   * Method to generate the HTML for the table
   *
   * @return string The HTML
   */
  public function generateTableHtml($tableName = 'datatable')
  {
    $content = '
      <table id="'.$tableName.'" class="table table-striped table-bordered dTableR">
          <thead><tr>';
    foreach ($this->_columns as $label => $title) {
      $content.='<th>'.$title.'</th>';
    }
    $content.='</tr></thead><tbody><tr>
             <td class="dataTables_empty" colspan="'.count($this->_columns).'">Loading data from server</td>
              </tr></tbody>
      </table>';
    if (!empty($this->_jsConfig)) {
      $content.= '<script type="text/javascript">'.$this->_jsConfig.'</script>';
    }
    return $content;
  }

  /**
   * Method to get the data.
   *
   * return array The retrieved data. Format:
   *              "sEcho" => $this->_params['sEcho'],
   *              "iTotalRecords" => $countRows,
   *              "iTotalDisplayRecords" => $countDisplayRows,
   *              "aaData" => $aaData
   */
  abstract protected function getData();

  /**
   * Method to process all the get params and filter them
   *
   * @param array $params Request params
   */
  protected function _setGetParams($params)
  {
    $this->_logger->info(__METHOD__);
    $this->_logger->debug(__METHOD__.' params: '.print_r($params, true));
    if (!empty($this->_extraGetOptions)) {
      $this->_getOptions = array_merge($this->_getOptions, $this->_extraGetOptions);
    }
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

}