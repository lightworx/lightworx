<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: ClassLoader.php 25 2011-10-03 14:07:25Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

use Lightworx\Exception\HttpException;
use Lightworx\Component\Iterator\FileFilterIterator;

/**
 * ClassLoader using spl_autoload_register function to automatically loading class
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @see http://php.net/manual/en/ref.spl.php
 * @since version 0.1
 * @version $Id: ClassLoader.php 25 2011-10-03 14:07:25Z Stephen.Lee $
 */
class ClassLoader
{
	/**
	 * The $classes to store the imported class.
	 */
	public static $classes = array();
	
	/**
	 * All the signup namespace store in the $namespaces.
	 * @var array
	 */
	protected static $namespaces = array();
	
	public static function register()
	{
		spl_autoload_register(array('\Lightworx\Foundation\ClassLoader','loadClass'));
	}


	public static function registerNamespace($namespace,$path)
	{
		self::$namespaces[$namespace] = $path;
	}
	
	/**
	*  Register a namespace to $namespaces
	*  @param array $namespaces You can specify your application namespace or vendors.
	*/
	public static function registerNamespaces(array $namespaces)
	{
		self::$namespaces = array_merge(self::$namespaces,$namespaces);
	}

	public static function hasNamespace($name)
	{
		return isset(self::$namespaces[$name]);
	}
	
	/**
	*  Return the all namespace
	*  @return array
	*/
	public static function getNamespaces()
	{
		return self::$namespaces;
	}
	
	public static $aliasClasses = array();
	
	/**
	*  Load class and auto include the class file.
	*  @param string Class name, may contain namespace
	*/
	public static function loadClass($class)
	{
		if(isset(self::$classes[$class]) and file_exists(self::$classes[$class]))
		{
			require(self::$classes[$class]);
			if(!class_exists($class))
			{
				throw new HttpException(404,"Not found the class: ".$class);
			}
			return;
		}
		
		if(isset(self::$aliasClasses[$class]))
		{
			$class = self::$aliasClasses[$class];
		}
		
		$namespace = explode('\\',$class);
		if(!array_key_exists($namespace[0],self::$namespaces))
		{
			throw new \RuntimeException("Not found name space: ".$class);
		}
		
		$file = self::$namespaces[$namespace[0]].str_replace('\\',DIRECTORY_SEPARATOR,$class).'.php';
		if(!file_exists($file))
		{
			throw new \RuntimeException("file:".$file." not found");
		}
		
		require $file;
	}
	
	/**
	*  Import specify class file or package to the property $classes
	*  @param mixed $file string, you can set the file path, or set a file.
	*  @param string extension specify the name of file, defaults to '.php'.
	*  @param boolean if the class name was exist in the property $classes,
	*         You can set true to force override the original class name.
	*  @param string specify the class base path, defaults to null, 
	*         That means the default from the application path as a base path.
	*/
	public static function import($file,$extension='.php',$force=false,$baseDir=null)
	{
		if(is_file($file) and ($className = basename($file,$extension))!='')
		{
			if(array_key_exists($className,self::$classes) and $force===true)
			{
				self::$classes[$className] = $file;
			}
			if(!array_key_exists($className,self::$classes))
			{
				self::$classes[$className] = $file;
			}
			return ;
		}
		
		if(is_string($file) and strpos($file,'.'))
		{
			if($baseDir===null)
			{
				$baseDir = Kernel::getApplicationPath();
			}
			
			$iterator = self::getRecursiveIteratorIterator($baseDir.str_replace(array('.','*'),array('/',''),$file));
			
			if(!($iterator instanceof \RecursiveIteratorIterator))
			{
				throw new \RuntimeException("The iterator must be instance of RecursiveIteratorIterator.");
			}
			
			foreach($iterator as $phpFile)
			{
				if($phpFile->isFile())
				{
					self::$classes[$phpFile->getBasename($extension)] = $phpFile->getPathname();
				}
			}
		}
	}
	
	static public function helperImport($path)
	{
		$iterator = self::getRecursiveIteratorIterator($path);
		foreach($iterator as $file)
		{
			include_once($file->getPathname());
		}
	}
	
	public function registerClassAlias($aliasName,$className,$force=false)
	{
		if($force===false and isset(self::$aliasClasses[$aliasName]))
		{
			throw new \RuntimeException("The class");
		}
		self::$aliasClasses[$aliasName] = $className;
	}
	
	/**
	 * This method for FileFilterIterator
	 */
	static public function classFilter($fileFilterIterator=null,$filterExtension='php',$containSubDir=false)
	{
		if($containSubDir===true and $fileFilterIterator!==null and $fileFilterIterator->current()->isDir())
		{
			return true;
		}
		
		if($fileFilterIterator!==null and $fileFilterIterator->current()->getExtension()!=$filterExtension)
		{
			return false;
		}
		return true;
	}
	
	static public function getRecursiveIteratorIterator($baseDir)
	{
		$dirIterator = new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$filterIterator = new FileFilterIterator($dirIterator);
		$filterIterator->acceptMethod = '\Lightworx\Foundation\ClassLoader::classFilter';
		return new \RecursiveIteratorIterator($filterIterator, \RecursiveIteratorIterator::SELF_FIRST);
	}
	
	// There is a temporary method to implement the class map cache.
	static public $classMapCacheFilename = 'classMap.php';
	
	static public $classMapCacheTimeout = 0;
	
	/**
	 * This property is valid just in the production mode.
	 * @var boolean Defaults to true, that means enable the class map cache for the mode production.
	 */
	static public $enableClassMapCache = true;

	static public function createClassMapCache(array $classMap=array())
	{
		if(strtolower(RUNNING_MODE)=='production')
		{
			$cacheFile = \Lightworx::getApplication()->getRuntimePath().self::$classMapCacheFilename;
			if(!file_exists($cacheFile))
			{
				// There should be merge with the old class map.
				$classMapCache = $classMap===array() ? self::$classes : $classMap;
				file_put_contents($cacheFile,"<?php \n return ".var_export($classMapCache,true));
			}
		}
	}
}