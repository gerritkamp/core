<?php

/**
 * Core_Populate, manages populating of data, usually for test purposes
 *
 * @category   Core
 * @package    Core_Populate
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Populate
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Populate
{
  protected $_logger;
  protected $_params;

  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
  }

  /**
   * Method to get a random number
   *
   * @param  integer $min Minimum
   * @param  integer $max Maximum
   *
   * @return integer The random number
   */
  public function getRandomInteger($min=0, $max=1000)
  {
    return mt_rand($min, $max);
  }

  /**
   * Method to get a random string
   *
   * @param  integer $length      The length of the string
   * @param  boolean $withNumbers Should it include numbers?
   *
   * @return string The random string
   */
  public function getRandText($length=10, $withNumbers=false)
  {
    $keys = array_merge(range('a', 'z'), range('A', 'Z'));
    if ($withNumbers) {
      $keys = array_merge($keys, range(0,9));
    }
    $key = '';
    for($i=0; $i < $length; $i++) {
        $key.= $keys[array_rand($keys)];
    }
    return $key;
  }

  /**
   * Method to generate a random but real domain type
   *
   * @return string The domain type
   */
  public function getRandomDomainType()
  {
    $types = array("com", "net", "info", "co", "tv");
    $key = array_rand($types);
    return $types[$key];
  }

  /**
   * Method to get a random email
   *
   * @return string The random email
   */
  public function getRandomEmail()
  {
    $numberFirst = $this->getRandomInteger(2, 8);
    $firstPart = $this->getRandText($numberFirst);
    $numberSecond = $this->getRandomInteger(2, 10);
    $secondPart = $this->getRandText($numberSecond);
    return $firstPart.'@'.$secondPart.'.'.$this->getRandomDomainType();
  }

  /**
   * Method to get a random phonenumber
   *
   * @param  string $type number type
   *                      Options: US, USPluxExt, International,
   *                      InternationalPlusExt
   *
   * @return string The phone number
   */
  public function getRandomPhoneNumber($type='US')
  {
    switch ($type) {
      case 'US':
        $firstPart = $this->getRandomInteger(200, 999);
        $secondPart = $this->getRandomInteger(100, 999);
        $thirdPart = $this->getRandomInteger(1000, 9999);
        $phoneNumber = '('.$firstPart.') '.$secondPart.'-'.$thirdPart;
        break;
      case 'USPlusExt':
        break;
      case 'International':
        break;
      case 'InternationalPlusExt':
        break;
    }
    return $phoneNumber;
  }

  /**
   * Method to return a random item from a picklist
   *
   * @param  array $list The picklist
   *
   * @return mixed The random item
   */
  public function getRandomPicklistContent($list)
  {
    $key = array_rand($list);
    return $list[$key];
  }

  /**
   * Method to get a random date, formatted
   *
   * @param  string $format The format of the date.
   *                        See http://php.net/manual/en/function.date.php
   * @param  string $start  The start date
   * @param  string $end    The end date
   *
   * @return string The formatted random date
   */
  public function getRandomDate(
    $start='01-01-1900', $end='12-31-2020'
  )
  {
    $startTimestamp = strtotime($start);
    $endTimestamp = strtotime($end);
    return $this->getRandomInteger($startTimestamp, $endTimestamp);
  }

  /**
   * Method to generate a bunch of datebase records
   *
   * @param  integer $items     The number of records to be created
   * @param  string  $model     The model to be used for inserting data
   * @param  string  $addMethod The method to be used for inputting data
   * @param  string  $filter    The input filter type used for inserting data
   *
   * @return boolean true upon success, false otherwise
   */
  public function populateItem($items, $model, $addMethod, $filter=null)
  {
    $this->_logger->info(__METHOD__);
    try {
      for ($i=0; $i<$items; $i++){
        // generate data
        $data = $this->_generateData($this->_params);
        $modelObj = new $model();
        // filter it if a filter is given
        if (!empty($filter)) {
          $filterObj = new $filter($data);
          if ($filterObj->isValid()) {
            $modelObj->$addMethod($filterObj->getCleanData());
          } else {
            $this->_logger->warn(__METHOD__.' data not valid: '.print_r($filterObj->getMessages(), true));
            $this->_logger->debug(__METHOD__.' clean data: '.print_r($filterObj->getCleanData()));
          }
        } else {
          $modelObj->$addMethod($data);
        }
      }
      return true;
    } catch (Exception $e) {
      $this->_logger->err(__METHOD__.' error: '.$e->getMessage());
      return false;
    }
  }

  protected function _generateData($params)
  {
    foreach ($params as $key => $details) {
      // recursively process detail arrays that do not have type
      if (!isset($details['type'])) {
        $results = $this->_generateData($details);
        $data[$key] = $results;
      } else {
        switch ($details['type']) {
          case 'date':
            $data[$key] = $this->getRandomDate($details['start'], $details['end']);
            break;
          case 'now':
            $data[$key] = time();
            break;
          case 'constant':
            $data[$key] = $details['constant'];
            break;
          case 'sha1':
            $data[$key] = sha1($this->getRandomInteger().microtime(true));
            break;
          case 'string':
            $data[$key] = $this->getRandText($details['length'], $details['with_numbers']);
            break;
          case 'name':
            $data[$key] = ucfirst(strtolower($this->getRandText(mt_rand(2, 8), false)));
            break;
          case 'phone':
            $data[$key] = $this->getRandomPhoneNumber($details['phone_type']);
            break;
          case 'email':
            $data[$key] = $this->getRandomEmail();
            break;
          case 'picklist':
            $data[$key] = $this->getRandomPicklistContent($details['list']);
            break;
        }
      }
    }
    return $data;
  }
}