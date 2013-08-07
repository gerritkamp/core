<?php

/**
 * Class to manage configuration for new sites
 *
 * @category   Core
 * @package    Core_Deploy
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Deploy
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Deploy_Configuration
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
  }

  /**
   * Method to update the configuration
   *
   * @param string $srcDir  The source dir
   * @param string $siteDir The site dir
   * @param string $env     The environment (development, staging, etc)
   * @param array  $params  The key - value params for the configuration
   *
   * @return Zend_Config_Ini configuration object
   */
  public function updateConfiguration($srcDir, $siteDir, $env, $params)
  {
    $this->_logger->info(__METHOD__);
    //copy default config to a separate location
    $configPath = $siteDir.'/var/configs/application.ini';
    copy($srcDir.'/application/configs/application.ini', $configPath);
    // create a config object
    $options = array('allowModifications' => true);
    $appConfig = new Zend_Config_Ini($configPath, $env, $options);
    // update the config data @todo rewrite this so that main level keys are not overwritten
    // probably best done with merging a fully new configuration into the old one
    foreach ($params as $key => $value) {
      $appConfig->__set($key, $value);
    }
    // write the new config
    $newConfig = new Zend_Config_Writer_Ini();
    $newConfig->write($configPath, $appConfig);
    // replace the APPLICATION_PATH variable
    //chmod($configPath, 0777);
    $config = file_get_contents($configPath);
    $config = str_replace(APPLICATION_PATH, $siteDir.'/application', $config);
    file_put_contents($configPath, $config);
    //chmod($configPath, 0744);
    $return['status'] = 'success';

    return $appConfig;
  }



  private function _removeDoubleSlashes($dir)
  {
    return str_replace('//', '/', $dir);
  }


}