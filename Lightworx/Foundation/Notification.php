<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Notification.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

/**
 * @package Lightworx.Foundation
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @since 0.1
 */
class Notification
{
	/**
	 * @var object store the instance variable of the current class.
	 */
	public static $instance = null;
	
	/**
	 * @var array using for store object
	 */
	public static $collections = array();
	
	private function __construct(){}

	/**
	 * Using pratten singleton to instance the class Notification
	 * @return object Notification instance 
	 */
	public static function getInstance()
	{
		if(!(self::$instance instanceof Notification))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Get all notification of the specify object
	 * @return array
	 */
	public function getNotify($obj)
	{
		if(is_string($obj) and isset(self::$collections[$obj]))
		{
			return self::$collections[$obj];
		}
		
		$className = \Lightworx::formatObjectName($obj);
		if(is_object($obj) and isset(self::$collections[$className]))
		{
			return self::$collections[$className];
		}
		return array();
	}
	
	/**
	 * Attachs an object to the collection
	 * @param mixed $obj
	 * @param boolean $serialize defaults to false
	 */
	public function attach($obj,$serialize=false)
	{
		if(!is_object($obj))
		{
			throw new \RuntimeException("Parameter \$obj must be an object.");
		}
		
		$className = \Lightworx::formatObjectName($obj);
		$instance = clone $obj;
		
		if($serialize===false)
		{
			self::$collections[$className][] = $instance;
		}else{		
			self::$collections[$className][] = serialize($instance);
		}
	}
	
	/**
	 * Detachs an object from the collection
	 * @param mixed $obj
	 * @return boolean
	 */
	public function detach($obj)
	{
		if(is_string($obj) and isset(self::$collections[$obj]))
		{
			unset(self::$collections[$obj]);
			return true;
		}
		
		if(is_object($obj) and $className = \Lightworx::formatObjectName($obj) and isset(self::$collections[$className]))
		{
			unset(self::$collections[$className]);
			return true;
		}
		return false;
	}
}