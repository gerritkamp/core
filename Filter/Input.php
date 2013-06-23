<?php
/**
 * Core_Filter_Input, manages the filtering and validation of sets of input
 *
 * @category   Core
 * @package    Core_Filter
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Filter
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Filter_Input extends Zend_Filter_Input
{

  /**
   * @var array
   */
  protected $_dirtyData = array();

  /**
   * @var array
   */
  protected $_cleanData = array();

  /**
   * @var array
   */
  protected $_validData = array();

  /**
   * @var boolean
   */
  protected $_isValid = false;

  /**
   * @var array Association of rules to filters.
   */
  protected $_filterRules = array();

  /**
   * @var array Association of rules to validators.
   */
  protected $_validatorRules = array();

  /**
   * @var array Association of messages for validators.
   */
  protected $_validatorMessages = array();

  /**
   * Constructor
   */
  public function __construct($dirtyData)
  {
    $this->_dirtyData = $dirtyData;
    $this->setFilters();
    $this->setValidators();
    $this->setValidatorMessages();
    $this->processInput();
  }

  /**
   * Placeholder function, to be overridden
   */
  public function setFilters()
  {

  }

  /**
   * Placeholder function, to be overridden
   */
  public function setValidators()
  {

  }

  /**
   * Placeholder function, to be overridden
   */
  public function setValidatorMessages()
  {

  }

  /**
   * Method to process the input.
   *
   */
  public function processInput()
  {
    // first filter the data
    $this->setData($this->_dirtyData);
    $this->_filter();
    $this->_cleanData = $this->_data;
    // then validate the filtered data
    $this->_validate();
    $this->_validData = $this->_validFields;
    // set isValid to true if there are no validation errors
    if (empty($this->_invalidErrors)) {
      $this->_isValid = true;
    };
  }

  /**
   * Getter for the clean data
   *
   * @return array Clean data array
   */
  public function getCleanData()
  {
    return $this->_cleanData;
  }

  /**
   * Getter for the valid data
   *
   * @return array Valid data array
   */
  public function getValidData()
  {
    return $this->_validData;
  }

  /**
   * Method to get custom error messages. If no custom is found, use a default.
   *
   * @return array Array with error messages
   */
  public function getErrorMessages()
  {
    $messages = array();
    $errors = $this->_invalidErrors;
    $defaultMessages = $this->getMessages();
    if ($errors) {
      foreach ($errors as $key => $value) {
        if (isset($this->_validatorMessages[$key])) {
          $messages[$key] = $this->_validatorMessages[$key];
        } else {
          if (!empty($defaultMessages[$key]) && is_array($defaultMessages[$key])) {
            // take the first default message
            $tmpKeys = array_keys($defaultMessages[$key]);
            $messages[$key] = $defaultMessages[$key][$tmpKeys[0]];
          }
        }
      }
    }
    return $messages;
  }

  /**
   * Method to return a string with all errors concatenated
   *
   * @return string The errors
   */
  public function getMessage()
  {
    $messages = '';
    $errors = $this->_invalidErrors;
    $defaultMessages = $this->getMessages();
    if ($errors) {
      foreach ($errors as $key => $value) {
        if (isset($this->_validatorMessages[$key])) {
          $messages.= $this->_validatorMessages[$key].' ';
        } else {
          if (!empty($defaultMessages[$key]) && is_array($defaultMessages[$key])) {
            // take the first default message
            $tmpKeys = array_keys($defaultMessages[$key]);
            $messages.= $defaultMessages[$key][$tmpKeys[0]].' ';
          }
        }
      }
    }
    return $messages;
  }

}