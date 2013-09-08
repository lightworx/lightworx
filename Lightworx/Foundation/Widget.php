<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Foundation;

use Lightworx\Foundation\ErrorHandler;
use Lightworx\Helper\Html;
use Lightworx\Controller\Controller;
use Lightworx\Component\Renderer\Renderer;
use Lightworx\Component\HttpFoundation\AssetManager;

abstract class Widget extends Object
{
	/**
	 * The widget id.
	 * @var string
	 */
	public $id;
	
	/**
	 * The default theme of current widget, defaults to 'Default'
	 * @var string
	 */
	public $defaultTheme = 'Default';
	
	/**
	 * The sequence of current widget
	 */
	public static $seq = array();
	
	/**
	 * The default message language file.
	 */
	public $language;
	
	/**
	 * The widget version will applied to the 
	 * request parameter of the resource file,
	 * like the following:
	 *           /assets/style.css?version20120102033
	 * @param mixed if not to specify the version, 
	 *        the default value is false, means have no version.
	 */
	public $version = false;
	
	public function __construct()
	{
		if(\Lightworx::getApplication()->language!==null)
		{
			$this->language = \Lightworx::getApplication()->language;
		}
	}
	
	/**
	 * Initialize widget, you may need to override this method.
	 */
	abstract public function init();
	
	/**
	 * Running the current widget.
	 */
	abstract public function run();
	
	/**
	 * Return an render instance
	 */
	public function getRender()
	{
		return new Renderer;
	}
	
	/**
	 * Format the specified class name, defaults to current class.
	 * @param object $class
	 * @return string
	 */
	public function getIdName($class=null)
	{
		if($class===null)
		{
			$class = $this;
		}
		return str_replace("\\","_",get_class($class));
	}

	/**
	 * Generates a widget id with the widget name and increment sequence.
	 * @param boolean $sharp whether use the sharp as the id prefix or not, 
	 *                       defaults to false.
	 * @return string
	 */
	public function getId($sharp=false)
	{
		$id = $this->getIdName();
		if($this->id===null)
		{
			if(isset(self::$seq[$id]))
			{
				$count = count(self::$seq[$id])+1;
				$seq = self::$seq[$id][] = $id.'_'.$count;
			}else{
				$seq = self::$seq[$id][] = $id.'_1';
			}
			$this->id = $seq;
		}
		return $sharp===false ? $this->id : '#'.$this->id;
	}

	/**
	 * Get id sequence, the sequence default should be a number.
	 * @param string $split The parameter split is used to separate the id string
	 *        defaults to '_' underscore
	 * @return string
	 */
	public function getIdSequence($split='_')
	{
		$ids = explode($split,$this->getId());
		$id = end($ids);
		return $id;
	}
	
	/**
	 * Gets a widget all ids, if the current widget id does not exist,
	 * that will be return an empty array.
	 * @return array
	 */
	public function getIds($sharp=true)
	{
		$id = str_replace("\\","_",get_class($this));
		$ids = array();
		if(self::$seq[$id])
		{
			foreach(self::$seq[$id] as $value)
			{
				$ids[] = $sharp===true ? '#'.$value : $value;
			}
		}
		return $ids;
	}
	
	public function getApp()
	{
		return \Lightworx::getApplication();
	}
	
	public function attachPackageScriptFile($packageName,$file,array $options=array())
	{
		AssetManager::attachPackageScriptFile($packageName,$file,$options);
	}

	public function attachPackageCssFile($packageName,$file,array $options=array())
	{
		AssetManager::attachPackageCssFile($packageName,$file,$options);
	}
	
	public function attachScriptFiles(array $file,array $options=array())
	{
		AssetManager::attachScriptFiles($file,$options);
	}
	
	public function attachCssFiles(array $file,array $options=array())
	{
		AssetManager::attachCssFiles($file,$options);
	}
	
	/**
	 * Attaching a script file
	 * @param mixed $file if it is an array, then that key should be a package name, 
	 *        and the value should be one file of the package.
	 *        if it is a string, that means only need to publishing one single file.
	 * @throws \RuntimeException if the file does not exist.
	 */
	public function attachScriptFile($file, array $options=array())
	{
		AssetManager::attachScriptFile($file,$options);
	}
	
	/**
	 * Attaching a css file
	 * @param mixed $file if it is an array, then that key should be a package name, 
	 *        and the value should be one file of the package.
	 *        if it is a string, that means only need to publishing one single file.
	 * @throws \RuntimeException if the file does not exist.
	 */
	public function attachCssFile($file, array $options=array())
	{
		AssetManager::attachCssFile($file,$options);
	}
	
	/**
	 * adding css code to page
	 * @param string $code
	 * @param mixed $flag
	 */
	public function addCssCode($code,$flag=null)
	{
		AssetManager::addCssCode($code,$flag);
	}
	
	/**
	 * adding script code to page
	 * @param string $code
	 * @param mixed $flag
	 */
	public function addScriptCode($code,$flag=null)
	{
		AssetManager::addScriptCode($code,$flag);
	}
	
	/**
	 * adding script code to page
	 * @param string $code
	 * @param boolean $enableReadyFunc whether use the jquery ready function or not,
	 *                defaults to true.
	 * @param mixed $flag this parameter is a key of the array
	 *              defaults to null
	 */
	public function addJqueryCode($code,$enableReadyFunc=true,$flag=null)
	{
		if($enableReadyFunc===true)
		{
			AssetManager::addJqueryCode($code,$flag);
		}else{
			$this->addScriptCode($code,$flag);
		}
	}
	
	/**
	 * Copying one directory or more directories to the specified directory
	 * @param array $directory that should be defined the source directory as a key
	 *                         and the value should be dest directory.
	 * @example copyDirectory(array(
	 *                          '/Users/Username/Sites/images/'=>'/Users/Another/Sites/images/',
	 *                          '/Users/Username/Sites/images/'=>'/Users/Another/Sites/images/',
	 *           ));
	 */
	public function copyDirectory(array $directories)
	{
		foreach($directories as $sourceDirectory=>$destDirectory)
		{
			AssetManager::recursiveCopy($sourceDirectory,$destDirectory);
		}
	}
	
	/**
	 * loading the client files of widget.
	 */
	public function publishResourcePackage($package,$config)
	{
		AssetManager::$resourcePackages[$package] = $config;
	}
	
	/**
	 * Attaching one or more script file
	 */
	public function attachPackageScriptFiles($packageName,array $sourceFiles,array $options=array())
	{
		AssetManager::attachPackageScriptFiles($packageName,$sourceFiles,$options);
	}
	
	/**
	 * Attaching one or more css file
	 */
	public function attachPackageCssFiles($packageName,array $sourceFiles,array $options=array())
	{
		AssetManager::attachPackageCssFiles($packageName,$sourceFiles,$options);
	}
	
	public function getPackagePublishPath($packageName,$allPath=false)
	{
		$path = AssetManager::getDestPath($packageName);
		if($allPath)
		{
			return $path;
		}
		return str_replace(PUBLIC_PATH,'/',$path);
	}
	
	public function evaluateExpression($_expression_,$_data_=array())
	{
		if(is_string($_expression_))
		{
			extract($_data_);
			return eval('return '.$_expression_.';');
		}
		$_data_[]=$this;
		return call_user_func_array($_expression_, $_data_);
	}

	/**
	 * This method gerenate the jquery plugin configure properties
	 * @param array $pluginProperties
	 * @return string
	 */
	public function getJQueryPluginProperties(array $pluginProperties,array $defaultProperties = array())
	{
		$properties = array();
		$pluginProperties = array_merge($defaultProperties,$pluginProperties);

		foreach($pluginProperties as $property=>$value)
		{
			$paramValue = '';
			if(is_string($value))
			{
				$paramValue = "'".$value."'";
			}
			if(is_array($value))
			{
				$paramValue = "'".json_encode($value)."'";
			}
			if(is_bool($value))
			{
				$paramValue = $value===true ? 'true' : 'false';
			}
			if(is_int($value))
			{
				$paramValue = $value;
			}
			if(strpos(ltrim(strtolower($value)),'function')===0 and substr(rtrim($value),-1)=='}')
			{
				$paramValue = $value;
			}
			// jquery code
			if(substr(trim($value),0,1)=='$' and (substr(trim($value),-1)==';' or substr(trim($value),-1)==')'))
			{
				$paramValue = $value;
			}
			// json format
			if(substr(trim($value),0,1)=='{' and substr(trim($value),-1)=='}')
			{
				$paramValue = $value;
			}
			$properties[] = $property.":".$paramValue;
		}
		return implode(",",$properties);
	}

	/**
	 * Convert the parameter key&value pairs to a string
	 * @param array $options
	 * @return string
	 */
	public function getHtmlOptions(array $options=array())
	{
		if($options===array())
		{
			return;
		}
		return Html::getHtmlOptions($options);
	}
	
	public function createUrl($rule,array $params=array(),$absoluteUrl=true,$mergeWithGetParam=false,$stripEmptyRequestParam=false)
	{
		$router = \Lightworx::getApplication()->router;
		return $router->createUrl($rule,$params,$absoluteUrl,$mergeWithGetParam,$stripEmptyRequestParam);
	}

	public function processOutput($output)
	{
		echo $output;
	}

	public function beforeOutput()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	public function afterOutput()
	{
		$output=ob_get_clean();
		$this->processOutput($output);
	}
}