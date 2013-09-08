<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Kernel.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

/**
 * The class Kernel be responsible for creating an application, then initialize configuration
 * @since version 0.1
 * @package Lightworx.Foundation
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @version $Id: Kernel.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */
class Kernel
{
	private static $loaded = false;
	private static $application;
	private static $appPath;
	
	
	/**
	*  initialize kernel
	*  @access private
	*  @param array $conf
	*/
	private static function initialize(array $conf)
	{
		$_ENV['APPLICATION_ENV'] = RUNNING_MODE;
		if(strtolower(RUNNING_MODE)==='production')
		{
			ini_set('display_errors', 0);
			$errorReporting = 0;
		}else{
			ini_set('display_errors', 1);
			$errorReporting = E_ALL;
		}
		isset($conf['errorReporting']) ? error_reporting($conf['errorReporting']) : error_reporting($errorReporting);
	}
	
	/**
	*  check the kernel whether loaded
	*  @access public
	*  @return boolean
	*/
	public function isLoaded()
	{
		return $this->loaded;
	}
	
	/**
	*  create and run the application
	*  @access public
	*/
	public static function app(array $conf)
	{
		self::initialize($conf);
		
		if(!isset($conf['ApplicationType']))
		{
			$conf['ApplicationType'] = 'WebApplication';
		}
		
		$applicationName = 'Lightworx\Foundation\\'.$conf['ApplicationType'];

		if(!class_exists($applicationName))
		{
			throw new \RuntimeException("The class ".$applicationName." not found.");
		}
		
		if(self::$application===null)
		{
			new $applicationName($conf);
			self::$loaded = true;
		}
		return self::$application;
	}
	
	/**
	*  Get the application instance
	*  @access public
	*  @return object
	*/
	static public function getApplication()
	{
		return self::$application;
	}

	/**
	 * The alias of the getApplication()
	 * @return Application
	 */ 
	static public function getApp()
	{
		return self::$application;
	}

	/**
	*  set the application instance to the property self::$application
	*  @access public
	*/
	public static function setApplication(Application $application)
	{
		if(self::$application===null)
		{
			self::$application = $application;
		}
	}
	
	/**
	*  return the application directory
	*  @access public
	*/
	public static function getApplicationPath()
	{
		if(self::$appPath===null)
		{
			return APP_PATH;
		}
		return self::$appPath;
	}
	
	public static function setApplicationPath($path)
	{
		self::$appPath = $path;
	}
	
	/**
	 * Formating the object name
	 * @param object $obj
	 */
	public static function formatObjectName($obj)
	{
		if(is_object($obj))
		{
			return str_replace("\\",".",get_class($obj));
		}
		if(is_string($obj))
		{
			return str_replace("\\",".",$obj);
		}
	}
	
	/**
	 * Formating the class name
	 * @param string $class
	 */
	public static function formatClassName($class)
	{
		if(is_string($class))
		{
			return str_replace(".","\\",$class);
		}
	}
	
	/**
	 * Creates a component
	 * @param string $name
	 * @return object
	 */
	public static function createComponent($class)
	{
		$properties = array();
		$args = func_get_args();
		array_shift($args);
		
		if(isset(self::getApplication()->components[$class]))
		{
			$properties = self::getApplication()->components[$class];
		}
		
		if(strpos($class,'.')!==false)
		{
			$class = str_replace('.','\\',$class);
		}
		
		if(isset($args[0]) and count($args[0])===0 and is_string($class))
		{
			$object = new $class;
		}
		
		if(isset($args[0]) and count($args[0])>0)
		{
			$instance = new \ReflectionClass($class);
			$object = call_user_func_array(array($instance,'newInstance'),$args[0]);
		}

		if(is_array($properties) and isset($object))
		{
			$properties = self::removeSystemComponentOptions($properties);
			foreach($properties as $property=>$value)
			{
				$method = 'set'.ucfirst($property);
				if(method_exists($object,$method))
				{
					$object->$method($value);
				}else{
					$object->{$property} = $value;
				}
			}
		}
		
		if(isset($object) and is_object($object) and is_object(self::getApplication()))
		{
			self::getApplication()->setComponent($object);
			return $object;
		}
	}
	
	static public function removeSystemComponentOptions($properties)
	{
		foreach($properties as $key=>$val)
		{
			if($key[0]=='_' and $key[strlen($key)-1]=='_')
			{
				unset($properties[$key]);
			}
		}
		return $properties;
	}

	static public function trace($type,$message,$others=array(),$time=null)
	{
		if($time===null)
			$time = microtime(true);

		$trace = \Lightworx::getApplication()->getComponent('Lightworx.Component.Logging.Trace');
		$trace->trace($type,$message,$others,$time);
	}
}