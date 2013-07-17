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
  protected $_sortSource = 0; // the id of the source that is used for sorting


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
    // is there search
    if (!empty($this->_params['sSearch'])) {
      $searchSources = array();
      foreach ($this->_searchFields as $sourceId => $fields) {
        if ($fields) {
          $searchSources[$sourceId] = $fields;
        }
      }
      // is it from multiple columns?
      if (count($searchSources) > 1) {
        $return = $this->_getCombinedSearch($searchSources);
      } else {
        $sourceIds = array_keys($searchSources);
        $return = $this->_getSingleSearch($sourceIds[0]);
      }
    } else {
      $return = $this->_getPagedData();
    }
    // each should return an array with 'data', 'count_total', and 'count_display'

    // get formattted data and ensure we only return configured columns
    foreach ($return['data'] as $key => $row) {
      // get formatted data
      $row = $this->formatRow($row);
      // ensure we got all columsn and not one more
      $rec = array();
      foreach ($fields as $index => $label) {
        $rec[$index] = $row[$label];
      }
      $aaData[] = $rec;
    }

    return array(
      "sEcho"                => $this->_params['sEcho'],
      "iTotalRecords"        => $return['count_total'],
      "iTotalDisplayRecords" => $return['count_display'],
      "aaData"               => $aaData
    );

  }

  /**
   * Method to get data when there is a search term across multiple sources
   *
   * @param array $searchSources Array with the search sources
   *
   * @return array with 'data', 'count_total', and 'count_display'
   */
  protected function _getCombinedSearch($searchSources)
  {
    $this->_logger->info(__METHOD__);
    $sortSourceId = $this->_getSourceForSorting();
    // get the count of records that are in the pre-search dataset
    $countAll = $this->getCountAll();
    // get from each relevant source the ID's that match the search criteria
    $filterIds = array();
    foreach ($searchSources as $sourceId => $fields) {
      $filterIds = array_values(array_unique(
        $filterIds, $this->_sources[$id]->getIdsBySearchData($this->_params)
      ));
    }
    // now use these ID's to get the paged data from the source that has the sort column
    $data = $this->_sources[$sortSourceId]->getPagedDataBySearchTerm($this->_params);
    $ids = array_keys($data);
    foreach ($searchSources as $sourceId => $fields) {
      if ($sourceId !== $sortSourceId) {
        $extraData = $this->_sources[$sourceId]->getDataByIds($ids);
        foreach ($extraData as $id => $results) {
          $data[$id] = array_merge($data[$id], $results);
        }
      }
    }

    return array(
      'count_total'   => $countAll,
      'count_display' => count($filterIds),
      'data'          => $data
    );
  }

  /**
   * Method to get data when there is a search term within one datasource
   *
   * @param array $searchSource The one search source
   *
   * @return @return array with 'data', 'count_total', and 'count_display'
   */
  protected function _getSingleSearch($searchSourceId)
  {
    $this->_logger->info(__METHOD__);
    $sortSourceId = $this->_getSourceForSorting();
    // get the count of records that are in the pre-search dataset
    $countAll = $this->getCountAll();
    // get from the search source the ID's that match the search criteria
    $filterIds = $this->_sources[$searchSourceId]->getIdsBySearchData($this->_params);
    // now use these ID's to get the paged data from the source that has the sort column
    $data = $this->_sources[$sortSourceId]->getPagedDataBySearchTerm($this->_params);
    // and now get the data from the other sources
    $ids = array_keys($data);
    foreach ($searchSources as $sourceId => $fields) {
      if ($sourceId !== $sortSourceId) {
        $extraData = $this->_sources[$sourceId]->getDataByIds($ids);
        foreach ($extraData as $id => $results) {
          $data[$id] = array_merge($data[$id], $results);
        }
      }
    }

    return array(
      'count_total'   => $countAll,
      'count_display' => count($filterIds),
      'data'          => $data
    );
  }

  /**
   * Method to get data when there is no search term
   *
   * @return @return array with 'data', 'count_total', and 'count_display'
   */
  protected function _getPagedData()
  {
    $this->_logger->info(__METHOD__);
    $sortSourceId = $this->_getSourceForSorting();
    // get from the sorting source the count of records that are in the dataset
    //$this->_logger->debug(__METHOD__.' sort source id'. $sortSourceId);
    // get the count of all records
    $countAll = $this->getCountAll();
    // now get the paged data from the source that has the sort column
    $data = $this->_sources[$sortSourceId]->getPagedData($this->_params);
    // and now get the data from the other sources
    $ids = array_keys($data);
    foreach ($searchSources as $sourceId => $fields) {
      if ($sourceId !== $sortSourceId) {
        $extraData = $this->_sources[$sourceId]->getDataByIds($ids);
        foreach ($extraData as $id => $results) {
          $data[$id] = array_merge($data[$id], $results);
        }
      }
    }

    return array(
      'count_total'   => $countAll,
      'count_display' => count($filterIds),
      'data'          => $data
    );
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
  }

  /**
   * Method to get the source for sorting
   *
   * @return integer The id of the source for sorting
   */
  protected function _getSourceForSorting()
  {
    $this->_logger->info(__METHOD__);
    if (!empty($this->_sortSource)) {
      return $this->_sortSource;
    } else {
      $sortKeys = array_keys($this->_columns);
      $sortColumn = $sortKeys[$this->_params['iSortCol_0']];
      foreach ($this->_searchFields as $sourceId => $fields) {
        if ($fields) {
          foreach ($fields as $field) {
            if ($sortColumn == $field) {
              $this->_sortSource = $sourceId;
            }
          }
        }
      }
    }
    if (!empty($this->_sortSource)) {
      return $this->_sortSource;
    } else {
      $this->_logger->err(__METHOD__.' could not find the sort column!');
      throw new ErrorException('Could not find sort column');
    }

  }

}