<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: WebApplication.php 29 2011-10-04 05:22:03Z Stephen.Lee $
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
class WebApplication extends Application
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
	public function run()
	{
		$this->setUser(new User);
		$this->processRequest();
		$this->controller = $this->createController($this->getControllerName());
		if(($action=$this->getRouter()->action)===null)
		{
			$action = $this->controller->defaultAction;
		}
		$this->getUser()->validateAccess($this->controller,$action);
		$this->getActiveComponent();
		$this->runController($this->controller,$action);
	}
	
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
	public function initializeModule($module)
	{
		$modules = isset($this->conf['modules']) ? $this->conf['modules'] : array();
		$moduleConf = include($modules[$module]['configure']);
		
		$this->setNotification(Notification::getInstance());
		$this->configure($this->conf,$moduleConf);
		$this->modulePath = $this->modules[$module]['modulePath'];
		Kernel::setApplicationPath($this->modulePath);
		
		if(isset($this->defaultTimeZone))
		{
			$this->setTimeZone($this->defaultTimeZone);
		}
		$this->importClass('.php',true,$this->modulePath);
		$this->processRequest();
	}
	
	/**
	 * Get the controller file.
	 * @return string
	 */
	public function getControllerPath($controllerName)
	{
		$controllerPath = $this->getApplicationPath().$this->controllerPath.$controllerName.'.php';
		if($this->router->module!='' and isset($this->modules[$this->router->module]))
		{
			$controllerPath = $this->modulePath.$this->controllerPath;
		}
		return $controllerPath;
	}
	
	/**
	 * Creates a controller, if cannot find the controller file, 
	 * that will to throw a HttpException
	 * @param string $controllerName
	 */
	public function createController($controllerName)
	{
		$controllerFile = $this->getControllerPath($controllerName);
		if(!file_exists($controllerFile))
		{
			throw new HttpException(404,"The controller ".$controllerName." cannot be found.");
		}

		$controller = new $controllerName;
		$this->setController($controller);
		$controller->setControllerName($controllerName);
		return $this->getController();
	}
	
	/** 
	 * check the controller is exist
	 */
	protected function processRequest()
	{
		$router = $this->getRouter();
		$this->setRequest($this->getComponent('Lightworx.Component.HttpFoundation.Request',$router));
		
		if($router->controller===null)
		{
			$router->controller = $this->defaultController;
		}
		$router->controllerName = $router->controller.$this->controllerExtension;
		$this->setControllerBaseName(ucfirst($router->controller));
		$this->setControllerName(ucfirst($router->controllerName));
	}
	
	/**
	 * @return boolean
	 */
	public function validateCsrfToken()
	{
		if(!($request=$this->getRequest()) instanceof Request)
		{
			throw new \RuntimeException('Cannot get the component Request.');
		}
		
		$csrfTokenName = \Lightworx::getApplication()->csrfTokenName;
		if(isset($_POST[$csrfTokenName]))
		{
			return $_POST[$csrfTokenName]==\Lightworx::getApplication()->getCsrfToken();
		}
		return $request->getCsrfToken()==\Lightworx::getApplication()->getCsrfToken();
	}
	
	/**
	 * get the Router instance
	 */
	public function getRouter()
	{
		if($this->router===null)
		{
			$this->router = new Router($this->route);
			
			$this->setComponent($this->router);
			$conf = Dispatcher::getActionConfig($this->router->controller,$this->router->action);
			$this->configure($this->conf,$conf);
		}
		return $this->router;
	}
	
	/**
	 * set object router
	 * @param Router $router
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;
	}
	
	/**
	 * run the controller
	 * @param Controller object
	 * @param string action name
	 * @param Router object router
	 */
	public function runController(Controller $controller,$action)
	{
		$response = $this->getComponent('Lightworx.Component.HttpFoundation.Response');
		$controller->setResponse($response);
		$controller->setAction($action);
		
		if($this->beforeController($controller))
		{
			$controller->run($action);
			$this->afterController($controller);
		}
	}

	/**
	 * This method is invoked before a Controller execute.
	 * @return boolean
	 */
	public function beforeController(Controller $controller)
	{
		$this->eventNotify('beforeController.'.$controller->getControllerBaseName(),$this);
		$this->eventNotify('beforeController.*',$this);
		return true;
	}
	
	/**
	 * This method is invoked after a Controller execute finished.
	 */
	public function afterController(Controller $controller)
	{
		$this->eventNotify('afterController.'.$controller->getControllerBaseName(),$this);
		$this->eventNotify('afterController.*',$this);
	}
	
	/**
	 * Get active component instance 
	 * if the configure have the key _autoload_ and that value is false, 
	 * it is not to automatically get the specifying component.
	 */
	protected function getActiveComponent()
	{
		foreach($this->components as $component=>$configure)
		{
			if(isset($configure['_autoload_']) and $configure['_autoload_']===false)
			{
				continue;
			}

			$instance = $this->getComponent($component);
			if(method_exists($instance,'execute'))
			{
				$instance->execute();
			}
		}
	}
}