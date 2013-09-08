<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Dispatcher.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */
 
namespace Lightworx\Foundation;

class Dispatcher extends Object
{
	public static function getActionConfig($controller,$action)
	{
		$config = self::getActionConfigFIle($controller);
		if(is_array($config) and array_key_exists($action,$config))
		{
			return $config[$action];
		}
		return array();
	}
	
	public static function getActionConfigFile($controller)
	{
		$configFile = \Lightworx::getApplicationPath().'config/controllers/'.strtolower($controller).'.php';
		if(!file_exists($configFile))
		{
			return false;
		}else{
			return require_once($configFile);
		}
	}
	
	/**
	 * Gets a authorization file via specifying controller
	 * @param string $controller
	 * @return array
	 */
	public static function getAuthorizationConfig($controller)
	{
		$configFile = \Lightworx::getApplicationPath().'config/auth/'.strtolower($controller).'.php';
		if(!file_exists($configFile))
		{
			return array();
		}else{
			return require_once($configFile);
		}
	}
}