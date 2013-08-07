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
    // create source links
    $siteFolder = $this->_removeDoubleSlashes($sites.'/'.$name);
    mkdir($siteFolder, 0744);
    $localSrc = $this->_removeDoubleSlashes($siteFolder.'/'.$app.'_src');
    symlink($src, $localSrc);
    foreach ($linkFolders as $folder) {
      symlink($localSrc.'/'.$folder, $siteFolder.'/'.$folder);
    }
    // make var folders and create links
    foreach ($varFolders as $folder) {
      $varFolder = $this->_removeDoubleSlashes($siteFolder.'/'.$folder);
      mkdir($varFolder, 0744);
      if (substr_count($folder, '/') == 0) {
        // ignoring subfolders
        symlink($varFolder, $siteFolder.'/'.$folder);
      }
    }
    // make www-data owner of site folder
    //$this->recurseChown($siteFolder, 'www-data');
    $cmd = "chown -R www-data ".$siteFolder;
    exec($cmd);

    // create webfolder symlink
    $webFolder = $this->_removeDoubleSlashes('/var/www/'.$name);
    symlink($siteFolder.'/public', $webFolder);
    $return['status'] = 'success';

    return $return;
  }


  private function _removeDoubleSlashes($dir)
  {
    return str_replace('//', '/', $dir);
  }

  /**
   * Method to recursively change ownership
   *
   * @param string $mypath The file path
   * @param string $uid    The user ID/name
   * @param string $gui    The group ID/name
   *
   * @return null
   */
  private function recurseChown($mypath, $uid, $gid=null)
  {
    $d = opendir ($mypath) ;
    while(($file = readdir($d)) !== false) {
      if ($file != "." && $file != "..") {
        $typepath = $mypath . "/" . $file ;
        if (filetype ($typepath) == 'dir') {
          $this->recurseChown($typepath, $uid, $gid);
        }
        chown($typepath, $uid);
        if ($gid) {
          chown($typepath, $gid);
        }
      }
    }
  }

}