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
      ServerName #name.myvirtualmentor.com
      Redirect permanent / https://#name.myvirtualmentor.com
    </VirtualHost>
    <VirtualHost *:443>
      ServerName #name.myvirtualmentor.com
      DocumentRoot /var/www/#url/public
      ErrorLog /var/log/apache2/#url.error.log
      CustomLog /var/log/apache2/#url.access.log combined
      SetEnv APPLICATION_ENV #environment
      SSLEngine On
      SSLCertificateFile /etc/apache2/ssl/apache.crt
      SSLCertificateKeyFile /etc/apache2/ssl/apache.key
      <Directory /var/www/>
        RewriteEngine On
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
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
   *
   * @return array Default status array
   */
  public function addNewVirtualHost($url, $environment)
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


  private function _removeDoubleSlashes($dir)
  {
    return str_replace('//', '/', $dir);
  }


}