<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Application.php 25 2011-10-03 14:07:25Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

use Lightworx\Component\Storage\FileStorage;
use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Routing\Router;
use Lightworx\Foundation\ErrorHandler;
use Lightworx\Foundation\CliErrorHandler;

/**
 * @since version 0.1
 * @package Lightworx.Foundation
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @version $Id: Application.php 25 2011-10-03 14:07:25Z Stephen.Lee $
 */
abstract class Application extends Object
{
	
	/**
	 * Set the Application name
	 */
	public $name = 'Application Name';
	
	/**
	 * Set the application character, defaults to utf-8
	 */
	public $charset = 'utf-8';
	
	/**
	 * Specifying application language, defaults to en_us
	 */
	public $language = 'en_us';
	
	/**
	 * Configure of application
	 */
	public $conf = array();
	
	/**
	 * Set default time zone for application
	 * @see http://www.php.net/manual/en/timezones.php
	 */
	public $defaultTimeZone = 'America/Los_Angeles';
	
	/**
	 * Setting error reporting level, 
	 * reference the error level of predefined constants
	 * @see http://www.php.net/manual/en/errorfunc.constants.php
	 */
	public $errorReporting  = E_ALL;
	
	/**
	 * Specifying application runtime directory name,
	 * that directory using for store the runtime data,
	 * default value is 'runtime/'
	 */
	public $runtimePath     = 'runtime/';
	
	/**
	 * Setting the error view path, if it does not exist, 
	 * that will using the inner error view path of framework.
	 * @var string
	 */
	public $errorViewPath;
	
	/**
	 * User component object
	 */
	public $user;
	
	/**
	 * The application theme
	 * @var string
	 */
	public $theme;
	
	/**
	 * The message file path
	 * @var string
	 */
	public $messagePath;
	
	/**
	 * The view message file path.
	 * @var string
	 */
	public $viewMessagePath;
	
	/**
	 * The storage object configure of the state
	 * @var array
	 */
	public $stateStorageConfigure = array();

	/**
	 * The application cache instance
	 * @var object
	 */
	public $cache;
	
	/**
	 * The application cache path
	 * @var string defaults to 'cache/'
	 */
	public $cachePath;

	public $importModuleClass = array();
	
	public $csrfToken;
	public $csrfTokenAlgorithm = 'md5';

	/**
	 * for method post
	 */ 
	public $csrfTokenName = 'csrf.token';
	
	/**
	 * SRFL HTTP request name.
	 */
	public $SRFLRequestParams = array('requestName'=>'_data','success'=>'1','fail'=>'0');
	
	public $SRFLEncryptKey = 'encrypt.request.key';
	
	/**
	 * Application bootstrap;
	 */
	public $appBootstrap;
	
	protected $loaded = false;
	protected $import;
	protected $viewPath;
	protected $applicationPath;
	protected $modelExtension;
	protected $ApplicationType;
	protected $fileStorage;
	protected $states = array();
	
	public $defaultController   = 'Main';
	public $defaultAction = 'index';
	public $controllerExtension = 'Controller';
	public $serviceControllerName = 'service';

	
	abstract public function run();
	abstract public function initialize();
	abstract public function initializeModule($module);
	abstract protected function processRequest();
	
	
	/**
	 * Creates an application and initialize
	 * @param array $config configure of application
	 */
	public function __construct(array $config)
	{
		$this->loaded = true;
		$this->conf = $config;
		$errorHandler = $this->getErrorHandler($config);

		register_shutdown_function(array(new $errorHandler,'lastError'));
		set_error_handler(array($this,"errorHandler"),error_reporting());
		set_exception_handler(array($this,"exceptionHandler"));
		
		\Lightworx::setApplication($this);
		if(($module=$this->isModule())!==false)
		{
			$this->initializeModule($module);
		}else{
			$this->initialize();
		}

		\Lightworx::setApplication($this);
		$this->createApplicationBootstrap();
	}

	/**
	 * Get the error handler for register a shutdown function.
	 * when occur a fatal error, that the shutdown function will be invoked for reporting the error.
	 * The method `getErrorHandler()` according to different app type to return different error handler.
	 * @param array $config
	 * @return string
	 */
	public function getErrorHandler(array $config)
	{
		$errorHandler = 'Lightworx\Foundation\ErrorHandler';
		$cliErrorHandler = 'Lightworx\Foundation\CliErrorHandler';
		if(isset($config['ApplicationType']))
		{
			return $config['ApplicationType']=='CliApplication' ? $cliErrorHandler : $errorHandler;
		}
		return $errorHandler;
	}

	/**
	 * Trigger an event and notify the related objects.
	 * @param string $event The event name
	 * @param object $object
	 */
	public function eventNotify($event,&$object)
	{
		\Lightworx\Foundation\Event::notify($event,$object);
	}
	
	/**
	 * Creates a bootstrap object, when the application startup.
	 */
	public function createApplicationBootstrap()
	{
		if($this->appBootstrap!==null and isset(ClassLoader::$classes[$this->appBootstrap]))
		{
			new $this->appBootstrap;
		}
	}
	
	/**
	 * When the application occurred an error,
	 * this method to take error and process error by configuration.
	 * @param Exception object $exception
	 */
	public function errorHandler($level,$message,$file,$line,$text)
	{
		restore_error_handler();
		restore_exception_handler();
		$trace=debug_backtrace();
		if(\Lightworx::getApplication()->ApplicationType!='CliApplication')
		{
			$error = new ErrorHandler($level,$message,$file,$line,$text);
		}else{
			$error = new CliErrorHandler($level,$message,$file,$line,$text);
		}
		exit(1);
	}
	
	/**
	 * When the application occurred an exception,
	 * this method have to catch the exception and handle error.
	 * @param Exception object $exception
	 */
	public function exceptionHandler($exception)
	{
		restore_error_handler();
		restore_exception_handler();
		if(\Lightworx::getApplication()->ApplicationType!='CliApplication')
		{
			$error = new ExceptionHandler($exception);
		}else{
			$error = new CliExceptionHandler($exception);
		}
		exit(1);
	}
	
	public function isModule()
	{
		$router = new Router(isset($this->conf['route']) ? $this->conf['route'] : array());
		if($router->module!='')
		{
			return $router->module;
		}
		return false;
	}
	
	/**
	 * Setting the configuration to each property of current object by configuration file.
	 * @param array $conf
	 */
	public function configure(array $conf, array $actionConf = array())
	{
		$this->conf = array_merge($conf,$actionConf);
		foreach($this->conf as $property=>$value)
		{
			if(isset($actionConf[$property]))
			{
				$this->conf[$property] = $actionConf[$property];
			}
			$this->$property = $this->conf[$property];
		}
	}
	
	/**
	 * When an application is created,
	 * the system will loading the specify objects,
	 * and import to the object ClassLoader by configuration the key import.
	 * @example 'import' => array('controllers.*','models.*','components.*'),
	 */
	public function importClass($extension='.php',$force=false,$baseDir=null)
	{
		// import the application class
		if(is_array($this->import))
		{
			foreach($this->import as $package)
			{
				ClassLoader::import($package,$extension,$force,APP_PATH);
			}
		}
		
		// import the module class
		if(is_array($this->importModuleClass))
		{
			foreach($this->importModuleClass as $modulePackage)
			{
				ClassLoader::import($modulePackage,$extension,$force,$baseDir);
			}
		}
	}
	
	/**
	 * set class alias in application
	 */
	public function setClassAlias($className,$sourceClassName,$force=false)
	{
		if($force===false and array_key_exists($className,ClassLoader::$alias))
		{
			return;
		}
		ClassLoader::$alias[$className]=$sourceClassName;
	}
	
	/**
	 * return the default time zone
	 * @return string
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}
	
	/**
	 * set the default timezone for application.
	 * you should specify the key defaultTimeZone at application config,
	 * about more details visit the php official site.
	 * @see http://www.php.net/manual/en/timezones.php
	 * @param string $timezone
	 */
	public function setTimeZone($timezone)
	{
		date_default_timezone_set($timezone);
	}
	
	/**
	 * Return the runtime path of application
	 * @return string
	 */
	public function getRuntimePath()
	{
		return APP_PATH.$this->runtimePath;
	}
	
	/**
	 * Get the file storage instance
	 * @param array $config the file storage parameters, defaults to an empty array
	 */
	public function getStateFileStorage(array $config=array())
	{
		if($this->fileStorage===null)
		{
			$this->fileStorage = new FileStorage;
			$this->fileStorage->setProperties($config);
		}
		return $this->fileStorage;
	}

	/**
	 * Gets a state by specified key
	 * @param string $key
	 * @return string if the state is exists.
	 * @return boolean if it not exists.
	 */
	public function getState($key)
	{
		if(isset(\Lightworx::getApplication()->states[$key]))
		{
			return \Lightworx::getApplication()->states[$key];
		}
		
		$storageConfig = $this->getStateStorageConfigure();
		$stateFile = $storageConfig['storagePath'].$storageConfig['storageFileName'];
		$storage = $this->getStateFileStorage($storageConfig);

		if(!file_exists($stateFile))
		{
			$stateKeys = array($key=>CryptString::generateState());
			$storage->data = serialize($stateKeys);
			$storage->save();
		}else{
			$stateKeys = unserialize($storage->getData());
			if(!isset($stateKeys[$key]))
			{
				$newKey = CryptString::generateState();
				$this->setState($key,$newKey);
				return $newKey;
			}
		}

		if(isset($stateKeys[$key]))
		{
			return $stateKeys[$key];
		}
		return false;
	}
	
	/**
	 * Sets a state by specified key
	 * @param string $key
	 * @param string $data
	 */
	public function setState($key,$data)
	{
		$storageConfig = $this->getStateStorageConfigure();
		$stateFile = $storageConfig['storagePath'].$storageConfig['storageFileName'];
		$storage = $this->getStateFileStorage($storageConfig);
		
		if(!file_exists($stateFile))
		{
			$stateKeys = array($key=>$data);
		}else{
			$stateKeys = unserialize($storage->getData());
			$stateKeys[$key] = $data;
		}
		$storage->data = serialize($stateKeys);
		$storage->save();
	}
	
	public function setStateStorageConfigure(array $config)
	{
		$this->stateStorageConfigure = $config;
	}
	
	/**
	 * Get the state file.
	 * @return array
	 */
	public function getStateStorageConfigure($stateFile='state.key')
	{
		if($this->stateStorageConfigure!==null and $this->stateStorageConfigure!==array())
		{
			return $this->stateStorageConfigure;
		}
		return array(
			'mode'=>'w+b',
			'enablePersistentData'=>true,
			'storagePath'=>$this->getRuntimePath(),
			'storageFileName'=>$stateFile,
		);
	}
	
	/**
	 * Get the application cache path.
	 * @return string
	 */
	public function getCachePath()
	{
		if($this->cachePath===null)
		{
			$this->cachePath = \Lightworx::getApplicationPath().'cache/';
		}
		return $this->cachePath;
	}
	
	/**
	 * Set the application cache path.
	 * @param string $cachePath the end of the cachePath must be a slash,
	 *               and it should be a full path, like the following:
	 *               '/sites/application_path/cache/'
	 */
	public function setCachePath($cachePath)
	{
		$this->cachePath = $cachePath;
	}
	
	public function getCsrfToken($csrfKey='csrf.token.key')
	{
		if($this->csrfToken===null)
		{
			$hashFunction = 'md5';
			if(function_exists($this->csrfTokenAlgorithm) or is_callable($this->csrfTokenAlgorithm))
				$hashFunction = $this->csrfTokenAlgorithm;

			$csrfToken = $this->getState($csrfKey);
			$this->csrfToken = $hashFunction($csrfToken.\Lightworx::getApplication()->user->getUserSalt());
		}
		return $this->csrfToken;
	}
	
	public function setCsrfToken($csrfToken)
	{
		$this->csrfToken = $csrfToken;
	}
}
