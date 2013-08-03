<?php

/**
 * Class to manage source code of deployments
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
class Core_Deploy_Source
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
   * Method to deploy source code. For now, do all deployment here. Future, create separate classes
   * for single server architecture, ssh deploy, ftp deploy etc.
   *
   * @param string $name        Url name of the new account
   * @param string $app         Type of app
   * @param array  $linkFolders Folders that need linked
   * @param array  $varFolders  Folders that contain specific var files
   * @param string $src         Path to source files (ie /home/gerrit/git/mvm)
   * @param string $sites       Path to sites (ie /home/gerrit/sites)
   *
   * @return array Default status array
   */
  public function deploySourceFiles($name, $app, $linkFolders, $varFolders, $src, $sites)
  {
    $this->_logger->info(__METHOD__);
    // create the webfolder
    $webFolder = $this->_removeDoubleSlashes('/var/www/'.$name);
    mkdir($webFolder, 0744);
    chown($webFolder, 'www-data');
    // create source links
    $localSrc = $this->_removeDoubleSlashes($webFolder.'/'.$app.'_src');
    symlink($src, $localSrc);
    foreach ($linkFolders as $folder) {
      symlink($localSrc.'/'.$folder, $webFolder.'/'.$folder);
    }
    // create var links
    $siteFolder = $this->_removeDoubleSlashes($sites.'/'.$name);
    mkdir($sitesFolder, 0744);
    foreach ($varFolders as $folder) {
      $varFolder = $this->_removeDoubleSlashes($sitesFolder.'/'.$folder);
      mkdir($varFolder, 0744);
      chown($varFolder, 'www-data');
      symlink($varFolder, $webFolder.'/'.$folder);
    }
    $return['status'] = 'success';

    return $return;
  }


  private function _removeDoubleSlashes($dir)
  {
    return str_replace('//', '/', $dir);
  }


}