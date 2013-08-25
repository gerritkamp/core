<?php

/**
 * HostUrl, creates host_url's for various scenarios
 *
 * @category   Core
 * @package    Core_Tools
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Tools
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Tools_HostUrl
{

  /**
   * Method to create the host_url for various scenarios
   *
   * @param boolean $https      Should https be used?
   * @param boolean $useAccount Should a sub-domain for a specific account be used?
   * @param string  $account    If account should be used, use this if its provided
   *
   * @return string the url
   */
  public static function getHostUrl($https=false, $useAccount=false, $account='')
  {
    $config = Zend_Registry::get('config');
    $protocal = $https ? 'https://' : 'http://';
    if ($useAccount) {
      if (empty($account)) {
        $account = $config->account;
      }
    } else {
      $account = '';
    }
    $domain = $config->domain;
    $subdomain = (APPLICATION_ENV == 'production') ? '.' : '.'.APPLICATION_ENV.'.';
    $hostUrl = $protocal.$account.$subdomain.$domain.'/';
    return $hostUrl;
  }

}