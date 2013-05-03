<?php
/**
 * The CountryZone model
 *
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Model
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Model_CountryZone extends Core_Model
{

  protected $_tableName = "core_country_zone";

  protected $_tableFields = array(
    array("id", "int(11) unsigned", "NO", "PRI", "", "auto_increment"),
    array("country_iso_2", "char(2)", "YES", "", "", ""),
    array("country_iso_3", "char(3)", "YES", "", "", ""),
    array("country_iso_nr", "int(11) unsigned", "YES", "", "0", ""),
    array("code", "varchar(45)", "YES", "", "", ""),
    array("name_local", "varchar(128)", "YES", "", "", ""),
    array("name_en", "varchar(50)", "YES", "", "", "")
  );

}