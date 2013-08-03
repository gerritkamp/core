<?php

/**
 * Database, manages user authentication
 *
 * @category   Core
 * @package    Core_Database
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Database
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Database
{

  protected $_libraryTables = array();

  /**
   * Constructor of the database class
   *
   * @param Zend_Db_Adapter $readDb   The read Db adapter
   * @param Zend_Db_Adapter $writeDb  The write Db adapter
   * @param string          $rootPath The root path to the application
   *
   * @return null
   */
  public function __construct($readDb=null, $writeDb=null, $rootPath=null)
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_readDb   = empty($readDb)   ? Zend_Registry::get('read_db')  : $readDb;
    $this->_writeDb  = empty($writeDb)  ? Zend_Registry::get('write_db') : $writeDb;
    $this->_rootPath = empty($rootPath) ? ROOT_PATH                      : $rootPath;
    $this->_existingTables = array();
  }

  /**
   * Method to update a database based on the model definitions
   *
   * @param boolean $exec         Should the statements be executed or just generated?
   * @param boolean $reset        Should database be reset?
   * @param boolean $returnError  Should error messages be returned?
   * @param boolean $deleteTables Should tables be deleted?
   *
   * @return mixed false upon failure, array with sql statements on success
   */
  public function updateDatabase(
    $exec=false, $reset=false, $returnError=true, $deleteTables=false
  )
  {
    $this->_logger->info(__METHOD__);
    // check for tables in database that are not defined
    $sql = "SHOW TABLES";
    $tables = $this->_readDb->query($sql)->fetchAll();
    if ($tables) {
      foreach ($tables as $table) {
        $keys = array_keys($table);
        $tableName = $table[$keys[0]];
        $this->_existingTables[] = $tableName;
      }
    }
    $results = array();

    // reset database if required
    if ($reset) {
      foreach ($this->_existingTables as $tableName) {
        $results['reset'][$tableName] = $this->dropTable($tableName, $exec);
      }
      $this->_existingTables = array();
    }

    // for each model in each library that has models
    $definedTables = array();
    try {
      $libPath = $this->_rootPath.'/library';
      $libs = scandir($libPath);
      foreach ($libs as $lib) {
        if (is_dir($libPath.'/'.$lib) &&  !in_array($lib, array('Zend', '.', '..'))) {
          $libContent = scandir($libPath.'/'.$lib);
          foreach ($libContent as $dir) {
            if (is_dir($libPath.'/'.$lib.'/'.$dir) && $dir == 'Model') {
              $models = scandir($libPath.'/'.$lib.'/Model');
              foreach ($models as $model) {
                if (!in_array($model, array('.', '..'))) {
                  $model = str_replace('.php', '', $model);
                  $className = $lib.'_Model_'.$model;
                  $class = new $className();
                  $tableDef = $class->getTableDefinition();
                  $tableName = $class->getTableName();
                  $definedTables[] = $tableName;
                  if ($tableName && $tableDef) {
                    $results[$tableName] = $this->updateTable($tableName, $tableDef, $exec);
                  }
                }
              }
            }
          }
        }
      }
      // check for tables in database that are not defined
      foreach ($this->_existingTables as $tableName) {
        if (!in_array($tableName, $definedTables)) {
          $results[$tableName] = $this->dropTable($tableName, $deleteTables);
        }
      }
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.$e->getMessage());
      if ($returnError) {
        return $e->getMessage();
      } else {
        return false;
      }
    }
    return $results;
  }

  /**
   * Method to update the database definition
   *
   * @param string  $tableName The table name
   * @param array   $tableDef  The table definition
   * @param boolean $exec      Should the statements be executed or just be generated and returned?
   *
   * @return string with sql statements
   */
  public function updateTable($tableName, $tableDef, $exec)
  {
    $this->_logger->info(__METHOD__);
    $return = '';
    // check the current database def
    if (in_array($tableName, $this->_existingTables)) {
      $sql = "SHOW COLUMNS FROM ".$tableName;
      $columns = $this->_readDb->query($sql)->fetchAll();
      // compare the two and create sql statements for the difference
      foreach ($tableDef as $field) {
        $toBe[$field[0]] = $field;
      }
      foreach ($columns as $field) {
        $fieldKeys = array_keys($field);
        $fieldValues = array_values($field);
        $asIs[$field[$fieldKeys[0]]] = $fieldValues;
      }
      //$this->_logger->debug(__METHOD__.' as is: '.print_r($asIs, true));
      //$this->_logger->debug(__METHOD__.' to be: '.print_r($toBe, true));
      foreach ($asIs as $key => $field) {
        if (empty($toBe[$key])) {
          $return.= $this->dropColumnFromTable($tableName, $key, $asIs[$key], $exec);
        }
      }
      foreach ($toBe as $key => $field) {
        if (empty($asIs[$key])) {
          $return.= $this->addColumnToTable($tableName, $field, $exec);
        } elseif ($asIs[$key] != $toBe[$key]) {
          $return.= $this->updateTableColumn($tableName, $key, $asIs[$key], $toBe[$key], $exec);
        }
      }
    } else {
      $return.= $this->createTable($tableName, $tableDef, $exec);
    }
    return $return;
  }

  /**
   * Method to create a table
   *
   * @param string  $tableName The table name
   * @param array   $tableDef  The table definition
   * @param boolean $exec      Should the statements be executed or just be generated and returned?
   *
   * @return The SQL statement
   */
  public function createTable($tableName, $tableDef, $exec)
  {
    $sql = 'CREATE TABLE IF NOT EXISTS `'.$tableName.'`(';
    foreach ($tableDef as $key => $field) {
      $sql.= $this->fieldDefToSql($field).',';
      if ($field[3] == 'PRI') {
        $index = 'PRIMARY KEY (`'.$field[0].'`)';
      }
    }
    $sql.= $index.') ENGINE=MyISAM DEFAULT CHARSET=utf8;';
    foreach ($tableDef as $key => $field) {
      // add other indices?
      if ($field[3] == 'MUL' || $field[3] == 'UNI') {
        $sql.= $this->addIndexSql($tableName, $field[0], $field[3]);
      }
    }
    $this->_logger->debug(__METHOD__.' sql: '.$sql);
    if ($exec) {
      $this->_writeDb->getConnection()->exec($sql);
    }
    return $sql;
  }

  /**
   * Method to drop a table
   *
   * @param string  $tableName The table name
   * @param boolean $exec      Should the statements be executed or just be generated and returned?
   *
   * @return The SQL statement
   */
  public function dropTable($tableName, $exec)
  {
    $sql = 'DROP TABLE `'.$tableName.'`;';
    if ($exec) {
      $this->_writeDb->getConnection()->exec($sql);
    }
    $this->_logger->debug(__METHOD__.' sql: '.$sql);
    return $sql;
  }

  /**
   * Method to add a column to a table
   *
   * @param string  $tableName The table name
   * @param array   $column    The column definition
   * @param boolean $exec      Should the statements be executed or just be generated and returned?
   *
   * @return The SQL statement
   */
  public function addColumnToTable($tableName, $column, $exec)
  {
    $sql = 'ALTER TABLE `'.$tableName.'` ADD '.$this->fieldDefToSql($column).';';
    if (!empty($column[3])) {
      $sql.= $this->addIndexSql($tableName, $column[0], $column[3]);
    }
    // make sure index and main table are done together
    $sql = str_replace(';ALTER TABLE ', ', ', $sql);
    if ($exec) {
      $this->_writeDb->getConnection()->exec($sql);
    }
    $this->_logger->debug(__METHOD__.' sql: '.$sql);
    return $sql;
  }

  /**
   * Method to update a column within a table
   *
   * @param string  $tableName The table name
   * @param string  $fieldName The column name
   * @param array   $asIs      An array with the various to be column definitions
   * @param array   $toBe      An array with the various to be column definitions
   * @param boolean $exec      Should the statements be executed or just be generated and returned?
   *
   * @return The SQL statement
   */
  public function updateTableColumn($tableName, $fieldName, $asIs, $toBe, $exec)
  {
    $sql = 'ALTER TABLE `'.$tableName.'` MODIFY '.$this->fieldDefToSql($toBe).';';
    if ($asIs[3] != $toBe[3]) {
      if (empty($asIs[3])) {
        $sql.= $this->addIndexSql($tableName, $toBe[0], $toBe[3]);
      } elseif (empty($toBe[3])) {
        $sql.= $this->dropIndexSql($tableName, $asIs[0], $exec);
      } else {
        // first drop and then create index
        $sql.= $this->dropIndexSql($tableName, $asIs[0], $exec);
        $sql.= $this->addIndexSql($tableName, $toBe[0], $toBe[3]);
      }
    }
    $sql = str_replace(';ALTER TABLE `'.$tableName.'` ',', ', $sql);
    if ($exec) {
      $this->_writeDb->getConnection()->exec($sql);
    }
    $this->_logger->debug(__METHOD__.' sql: '.$sql);
    return $sql;
  }

  /**
   * Method to drop a column from a table. Any associated indexes will be automatically dropped.
   *
   * @param string  $tableName  The table name
   * @param string  $columnName The column name
   * @param array   $asIs       An array with the various to be column definitions
   * @param boolean $exec       Should the statements be executed or just be generated and returned?
   *
   * @return The SQL statement
   */
  public function dropColumnFromTable($tableName, $columnName, $asIs, $exec)
  {
    $sql = 'ALTER TABLE `'.$tableName.'` DROP `'.$columnName.'`;';
    if ($exec) {
      $this->_writeDb->getConnection()->exec($sql);
    }
    $this->_logger->debug(__METHOD__.' sql: '.$sql);
    return $sql;
  }

  /**
   * Method to turn a field definition into its SQL equivalent
   *
   * @param array   $field Field definition
   * @param boolean $exec  Should the statements be executed or just be generated and returned?
   *
   * @return string SQL column definition
   */
  public function fieldDefToSql($field)
  {
    $sql = '';
    foreach ($field as $index => $item) {
      switch ($index) {
        case 0: // Field
          $sql.= ' `'.$item.'`';
          break;
        case 1: // Type
          $sql.= ' '.$item;
          break;
        case 2: // Null
          if ($item == 'NO') {
            $sql.= ' NOT NULL';
          }
          break;
        case 4: // Default
          if ($item !== null && $field[5] != 'auto_increment') {
            if ($item !== '') {
              $sql.= " DEFAULT '".$item."'";
            }
          }
          break;
        case 5: // Extra
          if ($item == 'auto_increment') {
            $sql.=' AUTO_INCREMENT';
          }
          break;
        case 3: // Key
        default:
          break;
      }
    }
    return $sql;
  }

  /**
   * Method to add an index to a table
   *
   * @param string  $tableName  The table name
   * @param string  $columnName The column name
   * @param string  $type       The index type (PRI, MUL, UNI)
   *
   * @return The SQL statement
   */
  public function addIndexSql($tableName, $columnName, $type)
  {
    $this->_logger->info(__METHOD__);
    $sql = '';
    switch ($type) {
      case 'MUL':
        $sql = 'ALTER TABLE `'.$tableName.'` ADD INDEX `'.$columnName.'` ( `'.$columnName.'` );';
        break;
      case 'PRI':
        $sql = 'ALTER TABLE `'.$tableName.'` ADD PRIMARY KEY `'.$columnName.'` ( `'.$columnName.'` );';
        break;
      case 'UNI':
        $sql = 'ALTER TABLE `'.$tableName.'` ADD UNIQUE `'.$columnName.'` ( `'.$columnName.'` );';
        break;
    }
    return $sql;
  }

  /**
   * Method to drop an index from a table
   *
   * @param  string  $tableName  The table name
   * @param  strint  $columnName The column name
   *
   * @return The SQL statement
   */
  public function dropIndexSql($tableName, $columnName, $exec)
  {
    $this->_logger->info(__METHOD__);
    $sql = 'ALTER TABLE `'.$tableName.'` DROP INDEX `'.$columnName.'`;';
    return $sql;
  }

  /**
   * Method to generate objects for all tables in the database
   *
   * @return None
   */
  public function generateObjectsFromTables()
  {
    $this->_logger->info(__METHOD__);
    // get all tables
    $sql = "SHOW TABLES";
    $tables = $this->_readDb->query($sql)->fetchAll();
    $this->setLibraryTables($tables);
    // get all columsn in our current db
    foreach ($tables as $table) {
      $keys = array_keys($table);
      $tableName = $table[$keys[0]];
      $sql = "SHOW COLUMNS FROM ".$tableName;
      $columns = $this->_readDb->query($sql)->fetchAll();
      // create objects
      $this->createSingleObject($tableName, $columns);
    }
    return count($tables);
  }

  /**
   * Method to get all core table names from the SHOW TABLES results
   *
   * @param  array The tables from SHOW TABLES
   *
   * @return  None
   */
  public function setLibraryTables($tables)
  {
    foreach ($tables as $table) {
      $keys = array_keys($table);
      $tableName = $table[$keys[0]];
      $nameParts = explode('_', $tableName);
      $library = ucfirst($nameParts[0]);
      $countNameParts = count($nameParts);
      for ($i=1; $i<$countNameParts; $i++) {
        $restNameParts[] = ucfirst($nameParts[$i]);
      }
      $restName = implode('', $restNameParts);
      $this->_libraryTables[$library] = $restName;
    }
  }

  /**
   * Method to import an sql file
   *
   * @param string  $file The SQL file
   * @param boolean $exex Should file be executed?
   *
   * @return None
   */
  public function importSqlFile($file, $exec)
  {
    $sql = file_get_contents($file);
    if ($exec) {
      try {
        $this->_writeDb->getConnection()->exec($sql);
        return true;
      } catch (Exception $e) {
        $this->_logger->debug(__METHOD__.' error: '.$e->getMessage());
        return false;
      }
    }
  }

  /**
   * Method to create one single object
   *
   * @param  string $tableName The table name
   * @param  array  $columns   The colum definitions
   *
   * @return None
   */
  public function createSingleObject($tableName, $columns)
  {
    $this->_logger->info(__METHOD__);
    $tmpDir = $this->_rootPath.'/tmp';
    $nameParts = explode('_', $tableName);
    $library = ucfirst($nameParts[0]);
    $countNameParts = count($nameParts);
    for ($i=1; $i<$countNameParts; $i++) {
      $restNameParts[] = ucfirst($nameParts[$i]);
    }
    $restName = implode('', $restNameParts);
    $extends = '';
    if ($library != 'Core' && in_array($restName, $this->_libraryTables['Core'])) {
      $extends = ' extends Core_Model_'.$restName;
    } elseif ($library == 'Core') {
      $extends = ' extends Core_Model'; // @todo, figure out if class extends another class
    }
    $content = '<?php
/**
 * The '.$restName.' model
 *
 * @category   '.$library.'
 * @package    '.$library.'_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   '.$library.'
 * @package    '.$library.'_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class '.$library.'_Model_'.$restName.$extends.'
{

  protected $_tableName = "'.$tableName.'";

  protected $_tableFields = array(';
foreach ($columns as $column) {
  $content.='
    array(';
  foreach ($column as $field => $value) {
    $content.='"'.$value.'", ';
  }
  $content = substr($content, 0, -2); // remove last comma)
  $content.= '),';
}
$content = substr($content, 0, -1); // remove last comma)
$content.='
  );

}';
    $dir = $tmpDir.'/'.$library;
    if (!is_dir($dir)) {
      mkdir($dir);
    }
    $fileName = $dir.'/'.$restName.'.php';
    file_put_contents($fileName, $content);
  }

}