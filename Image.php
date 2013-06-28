<?php

/**
 * Core_Image, manages uploading, manipulation, and storage of images
 *
 * @category   Core
 * @package    Core_Image
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Image
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Image
{
  protected $_config;
  protected $_logger;
  protected $_localTmpDir;
  protected $_uploadedFiles = array();
  protected $_maxImageSize  = 8;
  protected $_maxWidth      = 1600;
  protected $_maxHeight     = 1200;
  protected $_thumbWidth    = 200;
  protected $_thumbHeight   = 200;
  protected $_fileName;

  public function __construct($params=array())
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_config = Zend_Registry::get('config');
    $this->_localDirTmp = ROOT_PATH.'/'.$this->_config->app->local_storage->tmp;
    $this->_localDirImg = ROOT_PATH.'/'.$this->_config->app->local_storage->img;
    foreach ($params as $key => $value) {
      switch ($key) {
        case 'max_image_size':
          $this->_maxImageSize = $value;
          break;
        case 'max_width':
          $this->_maxWidth = $value;
          break;
        case 'max_height':
          $this->_maxHeight = $value;
          break;
        case 'thumb_width':
          $this->_thumbWidth = $value;
          break;
        case 'thumb_height':
          $this->_thumbHeight = $value;
          break;
      }
    }
  }

  /**
   * Method to determine the path for images
   *
   * @return string The image path. Should end with '/';
   */
  public function getImagePath()
  {
    switch ($this->_config->app->file_storage_method) {
      case 'local':
        return Zend_Registry::get('host_url').
          $this->_config->app->local_storage->img.'/';
        break;
      case 'rackspace':
        $this->storeOnRackspace();
        break;
      case 'amazons3':
        $this->storeOnAmazon();
        break;
    }
  }

  /**
   * Method to process the upload of one or more images
   * @param  array  $files     The parameters of the uploaded image
   * @param  string $inputName The name used in the upload form
   *
   * @return array Default return array
   */
  public function uploadImages()
  {
    $this->_logger->info(__METHOD__);
    $upload = new Zend_File_Transfer();
    $upload->addValidator('IsImage', false, array('gif', 'png', 'jpeg'));
    $upload->addValidator('FilesSize',
      false,
      array('min' => '10kB', 'max' => $this->_maxImageSize.'MB')
    );
    $upload->addValidator('ImageSize',
      false,
      array('minwidth' => 50, 'minheight' => 50)
    );

    // Returns all known internal file information
    $files = $upload->getFileInfo();
    //$this->_logger->debug(__METHOD__.' files: '.print_r($files, true));

    $errors = array();
    $counter = 0;

    foreach ($files as $file => $info) {
        // file uploaded ?
        if (!$upload->isUploaded($file)) {
            $errors[$counter][] = 'No image uploaded';
        }
        $realFileName = $upload->getFileName();

        // validators are ok ?
        if ($upload->isValid($file)) {
          // Set a new destination path and overwrites existing files
          $ext = strtolower(pathinfo($realFileName, PATHINFO_EXTENSION));
          $filename = sha1(microtime(true).$realFileName);
          $imageName = $filename.'.'.$ext;
          $upload->addFilter(
            'Rename',
            array('target' => $this->_localDirTmp.'/'.$imageName, 'overwrite' => true)
          );
          $info['image_name'] = $imageName;
          $info['filename'] = $filename;
          $this->_uploadedFiles[] = $info;
        } else {
          $errors[$counter][] = $realFileName.' does not seem to be a valid image.';
          $this->_logger->warn(__METHOD__.' errors: '.print_r($upload->getMessages(), true));
        }
        $counter++;
    }
    $upload->receive();

    // get additional image information and resize if needed
    foreach ($this->_uploadedFiles as $key => $image) {
      $imagePath = $this->_localDirTmp.'/'.$image['image_name'];
      $imageSize = getimagesize($imagePath);
      $w        = $imageSize[0];
      $h        = $imageSize[1];
      $mimeType = $imageSize['mime'];
      if ($w > $this->_maxWidth || $h > $this->_maxHeight) {
        // resize and save as jpg file, replacing the original source
        $newImage = $this->resizeImage(
          $this->_localDirTmp.'/'.$image['image_name'],
          $this->_localDirTmp.'/'.$image['filename'].'.jpg',
          $w, $h,
          $this->_maxWidth, $this->_maxHeight
        );
        $w = $newImage['width'];
        $h = $newImage['height'];
        $imagePath = $newImage['path'];
      }
      $imgParts = pathinfo($imagePath);
      $this->_uploadedFiles[$key]['width']     = $w;
      $this->_uploadedFiles[$key]['height']    = $h;
      $this->_uploadedFiles[$key]['mime_type'] = $mimeType;
      $this->_uploadedFiles[$key]['name']      = $imgParts['basename'];
      $this->_uploadedFiles[$key]['path']      = $this->_getStoragePath();
    }

    // store the images
    switch ($this->_config->app->file_storage_method) {
      case 'local':
        $this->_storeLocally();
        break;
      case 'rackspace':
        $this->storeOnRackspace();
        break;
      case 'amazons3':
        $this->storeOnAmazon();
        break;
    }
    $status = empty($errors) ? 'success' : 'error';

    // return the array with status and info so that DB storage is loosely coupled
    return array(
      'status' => empty($errors) ? 'success' : 'error',
      'results' => $this->_uploadedFiles,
      'errors' => $errors
    );
  }

  /**
   * Method to store images locally
   *
   * @return None
   */
  protected function _storeLocally()
  {
    $this->_logger->info(__METHOD__);
    foreach ($this->_uploadedFiles as $image) {
      rename(
        $this->_localDirTmp.'/'.$image['image_name'],
        $this->_localDirImg.'/'.$image['image_name']
      );
    }
  }

  public function storeOnAmazon()
  {

  }

  public function storeOnRackspace()
  {

  }

  protected function _getStoragePath()
  {
    $path = '';
    switch ($this->_config->app->file_storage_method) {
      case 'local':
        $path = $this->_config->app->local_storage->img;
        break;
      case 'rackspace':
        break;
      case 'amazons3':
        break;
    }
    return $path;
  }

  /**
   * Method to resize an image
   *
   * @param  string  $src  Full file path of source image
   * @param  string  $dest Full file path of destination. If null, replace source.
   * @param  integer $curW Current width
   * @param  integer $curH Current height
   * @param  integer $maxW Maximum width
   * @param  integer $maxH Maximum height
   * @param  integer $newW New width. If empty, we use maxW/maxH, else this
   * @param  integer $newH New height. If empty, we use maxW/maxH, else this
   *
   * @return array Array with filename, width, and height of the new image
   */
  public function resizeImage(
    $src, $dest=null, $curW=0, $curH=0, $maxW=100, $maxH=100, $newW=0, $newH=0
  )
  {
    $this->_logger->info(__METHOD__);
    if (!is_file($src)) {
      $this->_logger->err(__METHOD__.' image: '.$src.' is not a file');
      return false;
    }
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));

    // create the image resource
    switch($ext) {
      case 'jpg':
      case 'jpeg':
        $img = imagecreatefromjpeg($src);
        break;
      case 'gif':
        $img = imagecreatefromgif($src);
        break;
      case 'png':
        $img = imagecreatefrompng($src);
        break;
    }
    if (empty($img)) {
      $this->_logger->err(__METHOD__.' could not create image from: '.$src);
      return false;
    }

    // if no width or height is given, determine them
    if (empty($curW) || empty($curH)) {
      list($curW, $curH) = getimagesize($src);
    }

    // determine dimensions of new image
    $ratio = $curW / $curH;
    if (empty($newW) && empty($newH)) {
      if ($curW/$maxW > $curH/$maxH) {
        $newW = $maxW;
        $newH = floor($newW / $ratio);
      } else {
        $newH = $maxH;
        $newW = floor($ratio * $newH);
      }
    }

    // resize the image
    $newImage = imagecreatetruecolor($newW, $newH);
    $this->_logger->debug(__METHOD__.' newImage: '.print_r($newImage, true));
    imagecopyresampled($newImage, $img, 0, 0, 0, 0, $newW, $newH, $curW, $curH);

    // if no destination is given, replace the source
    $dest = empty($dest) ? $src : $dest;
    $ext = strtolower(strrchr($dest, '.'));
    switch($ext) {
      case '.jpg':
      case '.jpeg':
        imagejpeg($newImage, $dest, 95);
        break;
      case '.gif':
        imagegif($newImage, $dest);
        break;
      case '.png':
        imagepng($newImage, $dest);
        break;
    }
    return array(
      'path' => $dest,
      'width' => $newW,
      'height' => $newH
    );
  }

  public function cropImage()
  {

  }

}