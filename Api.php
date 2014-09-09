<?php

/**
 * Api, manages basic api functions.
 *
 * @category   Core
 * @package    Core_Api
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Api
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Api
{

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
  }

  public function createMessageHash($params)
  {
    $this->_logger->info(__METHOD__);
    $secretKey = Zend_Registry::get('secret_key');
    $publicKey = Zend_Registry::get('public_key');
    $params = json_encode($params, JSON_NUMERIC_CHECK);
    $hash = sha1($publicKey.$secretKey.$params);
    $this->_logger->debug(__METHOD__.' secret: '.$secretKey);
    $this->_logger->debug(__METHOD__.' public: '.$publicKey);
    $this->_logger->debug(__METHOD__.' hash: '.$hash);
    $this->_logger->debug(__METHOD__.' params: '.$params);
    return $hash;
  }

  /**
   * Method to verify if a request is valid
   *
   * @param array  $params    Submitted params. Must contain: hash, key, params.
   * @param string $secretKey Optional, the secret key corresponding to the public key.
   *
   * @return [type]            [description]
   */
  public function verifyRequest($params, $secretKey=null)
  {
    $this->_logger->info(__METHOD__);
    $valid = false;
    if (empty($params['hash']) || empty($params['key']) || !isset($params['params'])) {
      $this->_logger->err(__METHOD__.' error: missing params. Params: '.print_r($params, true));
    } else {
      $secretKey = $secretKey ? $secretKey : Zend_Registry::get('secret_key');
      $publicKey = $params['key'];
      $submittedHash = $params['hash'];
      $params = json_encode($params['params'], JSON_NUMERIC_CHECK);
      $hash = sha1($publicKey.$secretKey.$params);
      $this->_logger->debug(__METHOD__.' secret: '.$secretKey);
      $this->_logger->debug(__METHOD__.' public: '.$publicKey);
      $this->_logger->debug(__METHOD__.' hash: '.$hash);
      $this->_logger->debug(__METHOD__.' params: '.$params);
      if ($submittedHash == $hash) {
        $valid = true;
      } else {
        $this->_logger->err(__METHOD__.' request not valid with params: '.print_r($params, true));
      }
    }
    return $valid;
  }

  /**
   * Method to send a request and return the response
   * @todo - replace the Zend_Http_Client with something else, see if that is faster.
   *
   * @param string $url    The url to call
   * @param array  $params The params to send along
   * @param string $method The method. Default is POST
   * @param string $format The format. Default is json
   *
   * @return results array
   */
  public function sendMessage($url, $params, $method='POST', $format="json") {
    $this->_logger->info(__METHOD__.' method: '.$method);
    $hash = $this->createMessageHash($params);
    $return['status'] = 'error';
    try {
      switch ($format) {
        case 'xml':
          //@todo add and test xml endecoding
          break;
        case 'json':
        default:
          $params = array('params' => $params);
          break;
      }
      $params['hash'] = $hash;
      $params['key'] = Zend_Registry::get('public_key');
      switch (strtolower($method)) {
        case 'get':
          $options = array(
            CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($params),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false
          );
          break;
        case 'post':
        default:
          $options = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => http_build_query($params)
          );
          break;
      }
      $ch = curl_init();
      curl_setopt_array($ch, $options);
      $this->_logger->info(__METHOD__.' sending request..');
      if( ! $results = curl_exec($ch)) {
          trigger_error(curl_error($ch));
      }
      $this->_logger->info(__METHOD__.' got response..');
      curl_close($ch);
      switch ($format) {
        case 'xml':
          //@todo add and test xml decoding
          break;
        case 'json':
        default:
          $return = json_decode($results, true);
          break;
      }
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.$e->getMessage());
    }

    return $return;
  }

  /**
   * Method to send a request and return the response
   * @todo - replace the Zend_Http_Client with something else, see if that is faster.
   *
   * @param string $url    The url to call
   * @param array  $params The params to send along
   * @param string $method The method. Default is POST
   * @param string $format The format. Default is json
   *
   * @return results array
   */
  public function sendMessageOld($url, $params, $method='POST', $format="json")
  {
    $this->_logger->info(__METHOD__.' method: '.$method);
    $hash = $this->createMessageHash($params);
    $return['status'] = 'error';
    try {
      $client = new Zend_Http_Client();
      $client->setUri($url);
      switch ($format) {
        case 'xml':
          //@todo add and test xml endecoding
          break;
        case 'json':
        default:
          $params = array('params' => $params);
          break;
      }
      $params['hash'] = $hash;
      $params['key'] = Zend_Registry::get('public_key');
      switch (strtolower($method)) {
        case 'get':
          $client->setMethod(Zend_Http_Client::GET);
          $client->setParameterGet($params);
          break;
        case 'post':
        default:
          $client->setMethod(Zend_Http_Client::POST);
          $client->setParameterPost($params);
          break;
      }
      $this->_logger->info(__METHOD__.' sending request..');
      $response = $client->request();
      $this->_logger->info(__METHOD__.' got response..');
      if ($response->isError()) {
        $this->_logger->err(__METHOD__.' status: '.$response->getStatus().
          ' error: '.$response->getMessage());
      } else {
        $results = $response->getBody();
        $this->_logger->debug(__METHOD__.' status: '.$response->getStatus().
          ' data: '.$response->getBody());
        switch ($format) {
          case 'xml':
            //@todo add and test xml decoding
            break;
          case 'json':
          default:
            $return = json_decode($results, true);
            break;
        }
      }
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.$e->getMessage());
    }

    return $return;
  }
}