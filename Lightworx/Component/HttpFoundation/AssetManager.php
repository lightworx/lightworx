<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\HttpFoundation;

use Lightworx\Component\HttpFoundation\Request;
use Lightworx\Component\Iterator\FileFilterIterator;

class AssetManager
{
	private static $scriptFiles = array();
	private static $cssFiles = array();
	private static $scriptCodes = array();
	private static $cssCodes = array();
	private static $jqueryCodes = array();
	
	public static $assetPath;
	public static $withoutScheme = true;
	public static $resourceVersion = false;
	public static $resourcePackages = array();
	
	/**
	 * Return assets public url path
	 * @param array $domains defaults value is an empty array, that support multiple resource server.
	 * @return string
	 */
	public static function getAssetPublicUrl(array $domains=array())
	{
		$domain = $url = '';
		if(count($domains)>0)
		{
			$domain = $domains[array_rand($domains)];
		}
		
		if($domain=="")
		{
			$domain = \Lightworx::getApplication()->request->getHostName();
		}

		if(self::$withoutScheme===true)
		{
			$scheme = '//';
		}else{
			$scheme = \Lightworx::getApplication()->request->isSecureConnection() ? 'https://' : 'http://';
		}
		
		return $scheme.$domain.'/assets/';
	}
	
	/**
	 * Return the client resource version
	 */
	public static function getVersion()
	{
		if(self::$resourceVersion!==false)
		{
			return '?'.self::$resourceVersion;
		}
	}
	
	/**
	 * Publish the asset file.
	 * @return string
	 */
	public static function publishAssetFiles(array $domains=array())
	{
		return self::publishCssFiles($domains)."\n".self::publishScriptFiles($domains);
	}

	/**
	 * Publish the Css files to client.
	 */ 
	static public function publishCssFiles(array $domains=array())
	{
		self::publishResourcePackage();
		self::publishResourceFile();
		$cssFiles = array();
		foreach(self::$cssFiles as $key=>$file)
		{
			if(isset($file['package']))
			{
				$filePath = self::getDestPath($file['package'],self::$resourcePackages[$file['package']]);
				$assetPath = substr($filePath,strlen(self::getAssetPath()));
				$assetFile = self::getAssetPublicUrl($domains).$assetPath.$file['file'];
			}else{
				$assetFile = self::getAssetPublicUrl($domains).$key.basename($file['file']);
			}
			
			$initAttributes = array(
				"rel"=>"stylesheet",
				"type"=>"text/css",
				"href"=>$assetFile.self::getVersion()
			);
			$attributes = array_merge($initAttributes,$file['options']);
			$cssFiles[] = '<link '.self::attributesToText($attributes).' />';
		}
		return implode("\n",$cssFiles)."\n";
	}

	/**
	 * Publish the Script files to client.
	 */ 
	static public function publishScriptFiles(array $domains=array())
	{
		self::publishResourcePackage();
		self::publishResourceFile();
		$scriptFiles = array();
		foreach(self::$scriptFiles as $key=>$file)
		{
			if(isset($file['package']))
			{
				$filePath = self::getDestPath($file['package'],self::$resourcePackages[$file['package']]);
				$assetPath = substr($filePath,strlen(self::getAssetPath()));
				$assetFile = self::getAssetPublicUrl($domains).$assetPath.$file['file'];
			}else{
				$assetFile = self::getAssetPublicUrl($domains).$key.basename($file['file']);
			}
			
			$initAttributes = array(
				"type"=>"text/javascript",
				"src"=>$assetFile.self::getVersion()
			);
			$attributes = array_merge($initAttributes,$file['options']);
			$scriptFiles[] = '<script '.self::attributesToText($attributes).'></script>';
		}
		return implode("\n",$scriptFiles)."\n";
	}
	
	/**
	 * Recursive copy all package resource files to asset path.
	 */
	static public function publishResourcePackage()
	{
		foreach(self::$resourcePackages as $packageName=>$config)
		{
			$filter = (isset($config['filter']) && is_array($config['filter'])) ? $config['filter'] : array();
			if(($dest = self::getDestPath($packageName,$config)) and !file_exists($dest))
			{
				self::recursiveCopy(self::getSourcePath($config),$dest,$filter);
			}
		}
	}
	
	/**
	 * Copy the resource file to asset path,
	 * if it does not exist.
	 */
	public static function publishResourceFile()
	{
		$clientFiles = array_merge(self::$cssFiles,self::$scriptFiles);
		foreach($clientFiles as $key=>$file)
		{
			$clientFile = self::getAssetPath().$key.basename($file['file']);

			if(strtolower(RUNNING_MODE)!='production' and file_exists($clientFile))
			{
				unlink($clientFile);
			}
			if(!file_exists($clientFile) and !isset($file['package']))
			{
				if(!is_dir(dirname($clientFile)))
				{
					mkdir(dirname($clientFile),0755,true);
				}
				copy($file['file'],$clientFile);
			}
		}
	}
	
	public static function mergePublishResourceFile(){}
	
	/**
	 * Return the source path of the resource package.
	 * @param array $config
	 * @return string
	 */
	public static function getSourcePath(array $config)
	{
		if(!isset($config['source']))
		{
			throw new \RuntimeException("Must to define the source path of the package ".$packageName.".");
		}
		if(!file_exists($config['source']))
		{
			throw new \RuntimeException("The source directory does not exist.");
		}
		return $config['source'];
	}
	
	/**
	 * Return the destination path of the resource package.
	 * @param string $packageName
	 * @param array $config
	 * @return string
	 */
	public static function getDestPath($packageName,array $config=array())
	{
		if(isset($config['dest']))
		{
			return $config['dest'];
		}
		return self::getAssetPath().self::generatePackagePath($packageName);
	}
	
	/**
	 * Get a part of package path.
	 * @param string $packageName
	 * @return string
	 */
	public static function generatePackagePath($packageName)
	{
		return substr(md5($packageName),0,6).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Return a part of resource file path.
	 * @param string $filename
	 * @return string
	 */
	public static function generateClientFilePath($filename)
	{
		return substr(md5($filename),0,6).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Recursive copying specified source directory to 
	 * specified dest directory if the dest directory does not exist, 
	 * that will create the directory by recursive way.
	 * @param string $source
	 * @param string $dest
	 * @param array $filter
	 */
	public static function recursiveCopy($source,$dest,array $filter=array())
	{
		$dirIterator = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
		$filterIterator = new FileFilterIterator($dirIterator);
		$filterIterator->filters = $filter;
		$iterator = new \RecursiveIteratorIterator($filterIterator, \RecursiveIteratorIterator::SELF_FIRST);

		foreach($iterator as $item)
		{
			if($item->isDir())
			{
				mkdir($dest.$iterator->getSubPathName(),0777,true);
		  	}else{
				if(!is_dir($dest))
				{
					mkdir($dest,0755,true);
				}
				copy($item,$dest.$iterator->getSubPathName());
			}
	  	}
	}
	
	/**
	 * Return the asset path, if the self::$assetPath is null,
	 * that will return a default value is:
	 *                     PUBLIC_PATH.'assets'.DIRECTORY_SEPARATOR;
	 * @return string
	 */
	public static function getAssetPath()
	{
		if(self::$assetPath===null)
		{
			self::$assetPath = PUBLIC_PATH.'assets'.DIRECTORY_SEPARATOR;
		}
		return self::$assetPath;
	}
	
	/**
	 * Set the property assetPath
	 * @param string $assetPath
	 */
	public static function setAssetPath($assetPath)
	{
		self::$assetPath = $assetPath;
	}
	
	public static function attachPackageCssFiles($packageName,array $sourceFiles,array $tagOptions=array())
	{
		foreach($sourceFiles as $sourceFile)
		{
			self::attachPackageCssFile($packageName,$sourceFile,$tagOptions);
		}
	}
	
	public static function attachPackageScriptFiles($packageName,array $sourceFiles,array $tagOptions=array())
	{
		foreach($sourceFiles as $sourceFile)
		{
			self::attachPackageScriptFile($packageName,$sourceFile,$tagOptions);
		}
	}
	
	/**
	 * Attaching one css file, from was already loaded package.
	 * @param string $packageName
	 * @param string $sourceFile
	 * @param array $tagOptions
	 */
	public static function attachPackageCssFile($packageName,$sourceFile,array $tagOptions=array())
	{
		if(!isset(self::$resourcePackages[$packageName]))
		{
			throw new \RuntimeException("Cannot found the package ".$packageName.".");
		}
		
		$id = self::generateClientFilePath($sourceFile);
		if(!isset(self::$cssFiles[$id]))
		{
			self::$cssFiles[$id] = array('file'=>$sourceFile,'options'=>$tagOptions,'package'=>$packageName);
		}
	}
	
	/**
	 * Attaching one script file, from was already loaded package.
	 * @param string $packageName
	 * @param string $sourceFile
	 * @param array $tagOptions
	 */
	public static function attachPackageScriptFile($packageName,$sourceFile, array $tagOptions=array())
	{
		if(!isset(self::$resourcePackages[$packageName]))
		{
			throw new \RuntimeException("Cannot found the package ".$packageName.".");
		}
		
		$id = self::generateClientFilePath($sourceFile);
		if(!isset(self::$scriptFiles[$id]))
		{
			self::$scriptFiles[$id] = array('file'=>$sourceFile,'options'=>$tagOptions,'package'=>$packageName);
		}
	}
	
	/**
	 * Attaching one or more css file
	 * @param array $files
	 * @param array $option the tags property
	 */
	public static function attachCssFiles(array $files, array $tagOptions=array())
	{
		foreach($files as $file)
		{
			self::attachCssFile($file,$tagOptions);
		}
	}
	
	/**
	 * Attaching one or more script file
	 * @param array $files
	 * @param array $option the tags property
	 */
	public static function attachScriptFiles(array $files, array $tagOptions=array())
	{
		foreach($files as $file)
		{
			self::attachScriptFile($file,$tagOptions);
		}
	}
	
	/**
	 * Attach a css file.
	 * @param string $file
	 * @param array $option the tags property
	 */
	public static function attachCssFile($file, array $tagOptions=array())
	{
		if(!is_string($file) and !file_exists($file))
		{
			throw new \RuntimeException("Cannot found the css file:".$file);
		}
		
		$id = self::generateClientFilePath($file);
		if(!isset(self::$cssFiles[$id]))
		{
			self::$cssFiles[$id] = array('file'=>$file,'options'=>$tagOptions);
		}
	}
	
	/**
	 * Attach a script file.
	 * @param string $file
	 * @param array $option the tags property
	 */
	public static function attachScriptFile($file, array $tagOptions=array())
	{
		if(!is_string($file) and !file_exists($file))
		{
			throw new \RuntimeException("Cannot found the script file:".$file);
		}
		
		$id = self::generateClientFilePath($file);
		if(!isset(self::$scriptFiles[$id]))
		{
			self::$scriptFiles[$id] = array('file'=>$file,'options'=>$tagOptions);
		}
	}
	
	/**
	 * Add a script code block.
	 * @param string $code
	 */
	public static function addScriptCode($code,$flag=null)
	{
		if($flag!==null)
		{
			self::$scriptCodes[is_object($flag) ? get_class($flag) : $flag] = $code;
		}else{
			self::$scriptCodes[] = $code;
		}
	}
	
	public static function addJqueryCode($code,$flag=null)
	{
		if($flag===null)
		{
			self::$jqueryCodes[] = $code;
		}else{
			self::$jqueryCodes[(is_object($flag) ? get_class($flag) : $flag)] = $code;
		}
	}
	
	/**
	 * Add a css code block.
	 * @param string $code
	 */
	public static function addCssCode($code,$flag=null)
	{
		if($flag!==null)
		{
			self::$cssCodes[is_object($flag) ? get_class($flag) : $flag] = $code;
		}else{
			self::$cssCodes[] = $code;
		}
	}
	
	/**
	 * Convert attributes from array to a string
	 * @param array $attributes
	 */
	public static function attributesToText(array $attributes)
	{
		$text = '';
		foreach($attributes as $attribute=>$value)
		{
			$text[] = $attribute.'="'.$value.'"';
		}
		return implode(" ",$text);
	}
	
	/**
	 * Publishing the script code
	 * @param boolean $compress
	 * @return string
	 */
	public static function loadScriptCode($compress=true)
	{
		$js = array();
		if(self::$scriptCodes!==array())
		{
			$js[] = implode("\n",self::$scriptCodes);
		}
		if(self::$jqueryCodes!==array())
		{
			$js[] = '$(document).ready(function(){'.implode("\n",self::$jqueryCodes).'});';
		}
		$code = implode("\n",$js);
		$code = $compress===true ? AssetCompresser::scriptCompresser($code) : $code;
		if($code!='')
		{
			return '<script type="text/javascript">'.$code.'</script>'."\n";
		}
	}
	
	/**
	 * Publishing the css code
	 * @param boolean $compress
	 * @return string
	 */
	public static function loadCssCode($compress=true)
	{
		if(count(self::$cssCodes)==0)
		{
			return ;
		}
		$cssCode = implode("\n",self::$cssCodes);
		$cssCode = $compress===true ? AssetCompresser::scriptCompresser($cssCode) : $cssCode;
		if($cssCode!='')
		{
			return '<style>'.$cssCode.'</style>'."\n";
		}
	}
}