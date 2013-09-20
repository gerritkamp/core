<?php

/**
 * Class to manage virtual hosts
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
class Core_Deploy_VirtualHost
{

  /**
   * @var Logger
   */
  protected $_logger;

  /**
   * @var Template for virtualhost configuration
   */
  protected $_template = '
    <VirtualHost *:80>
      ServerName #name.#domain
      Redirect permanent / https://#name.#domain/
    </VirtualHost>
    <VirtualHost *:443>
      ServerName #name.#domain
      DocumentRoot /var/www/#url
      ErrorLog /var/log/apache2/#url.error.log
      CustomLog /var/log/apache2/#url.access.log combined
      SetEnv APPLICATION_ENV #environment
      SetEnv ROOT_PATH #sites/#url
      SetEnv APPLICATION_PATH #sites/#url/application
      SSLEngine On
      SSLCertificateFile /etc/apache2/ssl/apache.crt
      SSLCertificateKeyFile /etc/apache2/ssl/apache.key
    </VirtualHost>';

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
   * @param string $url         Url of the new account
   * @param string $environment Environment
   * @param string $sites       The sites folder
   *
   * @return array Default status array
   */
  public function addNewVirtualHost($url, $environment, $sites, $domain)
  {
    $this->_logger->info(__METHOD__);
    // create config
    $config = $this->_template;
    $config = str_replace('#environment', $environment, $config);
    if ($environment == 'production') {
      $name = $url;
    } else {
      $name = $url.'.'.$environment;
    }
    $config = str_replace('#name', $name, $config);
    $config = str_replace('#url', $url, $config);
    $config = str_replace('#sites', $sites, $config);
    $config = str_replace('#domain', $domain, $config);
    $return['status'] = 'success';
    // write config
    $filename = '/etc/apache2/sites-available/'.$url;
    file_put_contents($filename, $config);
    // enable site
    $cmd = 'a2ensite '.$url;
    exec($cmd);
    // reload config
    $cmd = '/etc/init.d/apache2 reload';
    exec($cmd);

    return $return;
  }

  /**
   * Method to update the /etc/hosts file
   *
   * @param string $url The full url
   *
   * @return null
   */
  public function updateEtcHostsFile($url)
  {
    $this->_logger->info(__METHOD__);
    $data = '127.0.0.1       '.$url.'
';
    $file = '/etc/hosts';
    $f = fopen($file, "a+");
    file_put_contents($file, $data, FILE_APPEND);
    fclose($f);
  }


  private function _removeDoubleSlashes($dir)
  {
    return str_replace('//', '/', $dir);
  }


}