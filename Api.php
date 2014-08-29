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
   *
   * @param string $url    The url to call
   * @param array  $params The params to send along
   * @param string $method The method. Default is POST
   * @param string $format The format. Default is json
   *
   * @return results array
   */
  public function sendMessage($url, $params, $method='POST', $format="json")
  {
    $this->_logger->info(__METHOD__);
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