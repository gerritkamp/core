<?php
/**
 * The Country model
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
class Core_Model_Country extends Core_Model
{

  protected $_tableName = "core_country";

  protected $_tableFields = array(
    array("id", "int(10) unsigned", "NO", "PRI", "", "auto_increment"),
    array("iso_2", "char(2)", "YES", "", "", ""),
    array("iso_3", "char(3)", "YES", "", "", ""),
    array("iso_nr", "int(10) unsigned", "YES", "", "0", ""),
    array("parent_tr_iso_nr", "int(10) unsigned", "YES", "", "0", ""),
    array("official_name_local", "varchar(128)", "YES", "", "", ""),
    array("official_name_en", "varchar(128)", "YES", "", "", ""),
    array("capital", "varchar(45)", "YES", "", "", ""),
    array("tldomain", "char(2)", "YES", "", "", ""),
    array("currency_iso_3", "char(3)", "YES", "", "", ""),
    array("currency_iso_nr", "int(10) unsigned", "YES", "", "0", ""),
    array("phone", "int(10) unsigned", "YES", "", "0", ""),
    array("eu_member", "tinyint(3) unsigned", "YES", "", "0", ""),
    array("address_format", "tinyint(3) unsigned", "YES", "", "0", ""),
    array("zone_flag", "tinyint(4)", "YES", "", "0", ""),
    array("short_local", "varchar(70)", "YES", "", "", ""),
    array("short_en", "varchar(50)", "YES", "", "", ""),
    array("uno_member", "tinyint(3) unsigned", "YES", "", "0", "")
  );

}