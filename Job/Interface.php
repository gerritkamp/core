<?php
/**
 * Interface that all jobs should implement.
 *
 * @category   Core
 * @package    Core_Job
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Job
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
interface Core_Job_Interface
{
  /**
   * Method to perform the job
   *
   * @param $args Array with job arguments
   */
  public function perform($args=array());
}