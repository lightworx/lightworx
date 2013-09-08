<?php

namespace Lightworx\Foundation;

use \Lightworx\Foundation\ClassLoader;
use Lightworx\Component\HttpFoundation\UserAgent;

class Bootstrap
{
	public $userAgent;
	public $enableLocalEnv = true;

	public function __construct()
	{
		$this->init();
		$this->createUserAgent();
	}
	
	public function init(){}

	public function createUserAgent()
	{
		$this->userAgent = new UserAgent;
	}

	public function pluginRegister()
	{
		$pluginExtension = $this->getPluginExtension();
		$activatedPlugins = $this->getActivatedPlugins();
		
		foreach(ClassLoader::$classes as $className=>$file)
		{
			if(substr($className,-6)==$pluginExtension)
			{
				$classBaseName = str_replace($pluginExtension,'',$className);
				if(in_array($classBaseName,$activatedPlugins) and method_exists($className,'register'))
				{
					$obj = new $className;
					$obj->register();
				}
			}
		}
	}
	
	public function getPluginExtension()
	{
		return \Lightworx::getApplication()->pluginExtension;
	}
	
	public function getActivatedPlugins()
	{
		$activatedPlugins = \Lightworx::getApplication()->activatedPlugins;
		if(is_array($activatedPlugins))
		{
			return $activatedPlugins;
		}
		throw new \RuntimeException("The parameter activatedPlugins must be an array");
	}
}