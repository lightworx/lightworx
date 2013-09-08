<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Foundation;

use Lightworx\Controller\Controller;
use Lightworx\Controller\ServiceController;
use Lightworx\Component\Routing\Router;
use Lightworx\Component\Renderer\Renderer;
use Lightworx\Component\HttpFoundation\Request;
use Lightworx\Component\HttpFoundation\Response;
use Lightworx\Component\Userland\User;
use Lightworx\Exception\HttpException;
use Lightworx\Exception\FileNotFoundException;

/**
 * @since version 0.1
 * @package Lightworx.Foundation
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @version $Id$
 */
class CliApplication extends Application
{
	public $request;
	public $modelExtension = '';
	
	public $widgets;
	public $route = array();
	public $router;
	public $renderer;
	public $modules;
	public $controller;
	public $controllerName;
	public $controllerBaseName;
	public $modulePath;
	public $controllerPath = 'controllers/';
	
	/**
	 * The activated plugins list
	 */
	public $activatedPlugins = array();
	
	/**
	 * The plugin class extension, defaults to 'Plugin'
	 */
	public $pluginExtension = 'Plugin';
	
	/**
	 * Running the web application and processing request.
	 */
	public function run(){}
	
	/**
	 * Initialize the web application, register configuration and import class.
	 */
	public function initialize()
	{
		$this->setNotification(Notification::getInstance());
		$this->configure($this->conf);
		$this->setApplicationPath(Kernel::getApplicationPath());
		if(isset($this->defaultTimeZone))
		{
			$this->setTimeZone($this->defaultTimeZone);
		}
		$this->importClass();
	}
	
	/**
	 * Initialize the application module
	 */
	public function initializeModule($module){}
	public function isModule(){return false;}
	
	/**
	 * Get the controller file.
	 * @return string
	 */
	public function getControllerPath($controllerName){}
	
	/**
	 * Creates a controller, if cannot find the controller file, 
	 * that will to throw a HttpException
	 * @param string $controllerName
	 */
	public function createController($controllerName){}
	
	/** 
	 * check the controller is exist
	 */
	protected function processRequest(){}
	
	/**
	 * get the Router instance
	 */
	public function getRouter(){}
	
	/**
	 * run the controller
	 * @param Controller object
	 * @param string action name
	 * @param Router object router
	 */
	public function runController(Controller $controller,$action){}

	/**
	 * Get active component instance 
	 * if the configure have the key _autoload_ and that value is false, 
	 * it is not to automatically get the specifying component.
	 */
	protected function getActiveComponent(){}
}