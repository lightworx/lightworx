<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Controller.php 29 2011-10-04 05:22:03Z Stephen.Lee $
 */

namespace Lightworx\Controller;

use Lightworx\Foundation\Object;
use Lightworx\Component\Renderer\Renderer;
use Lightworx\Component\HttpFoundation\Request;
use Lightworx\Component\HttpFoundation\Response;
use Lightworx\Exception\HttpException;

class Controller extends Object
{
	
	public $layout        = 'main';
	public $defaultAction = 'index';
	public $layoutPath    = 'layouts/';
	public $theme;
	
	private $_module;
	private $_action;
	private $_controller;
	private $_route;
	
	protected $_model;
	protected $renderer;
	protected $request;
	protected $response;
	protected $actionPrefix = 'action';
	protected $actionSuffix = '';

	/**
	 * Gets a action name, if it is an avaliable action name,
	 * that will be returning the action name contained action prefix and suffix, if the suffix has been defined.
	 * @param string $name default null
	 */
	public function getActionName($name=null)
	{
		$action = \Lightworx::getApplication()->router->action;
		if($name!==null)
		{
			$action = $name;
		}
		return $this->actionPrefix.ucfirst($action).$this->actionSuffix;
	}
	
	/**
	 * Run the specified action of the controller.
	 * @param String $action
	 */
	public function run($action)
	{
		if($this->beforeAction($action))
		{
			$this->runAction($action);
			$this->afterAction($action);
		}else{
			throw new \RuntimeException("The controller method before".ucfirst($action)." should to return true.");
		}
	}

	/**
	 * This method will be invoked before to access the specified action.
	 * @param String $action
	 * @return boolean if the before{$action} method cannot be found, 
	 *                 that will be returns true.
	 */
	protected function beforeAction($action)
	{
		$this->registerControllerBeforeEvent($action);
		$method = 'before'.ucfirst($action);
		if(method_exists($this, $method))
		{
			return $this->{$method}();
		}
		return true;
	}
	
	/**
	 * When the request finished, this method will be invoked, 
	 * This method could be overridden in the sub controller.
	 * @param String $action
	 */
	protected function afterAction($action)
	{
		$this->registerControllerAfterEvent($action);
		$method = 'after'.ucfirst($action);
		if(method_exists($this, $method))
		{
			$this->{$method}();
		}
	}

	protected function registerControllerBeforeEvent($action)
	{
		\Lightworx::getApplication()->eventNotify('controller.'.$this->getControllerBaseName().'.beforeAction.'.$action, $this);
	}

	protected function registerControllerAfterEvent($action)
	{
		\Lightworx::getApplication()->eventNotify('controller.'.$this->getControllerBaseName().'.afterAction.'.$action, $this);
	}
	
	/**
	 * Return a route instance
	 */
	protected function getRoute()
	{
		return $this->_route;
	}
	
	/**
	 * Sets a route instance
	 * @param \Lightworx\Component\Routing\Route $route
	 */
	public function setRoute(Route $route)
	{
		$this->_route = $route;
	}

	/**
	 * Return the controller name
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}
	
	/**
	 * Sets a controller name
	 * @param String $name
	 */
	public function setControllerName($name)
	{
		$this->controllerName = $name;
	}
	
	/**
	 * Get the controller name without controller extension.
	 * @return string
	 */
	public function getControllerBaseName()
	{
		$controllerExtensionLength = strlen(\Lightworx::getApplication()->controllerExtension);
		return substr($this->getControllerName(),0,-$controllerExtensionLength);
	}
	
	protected function getController()
	{
		return $this->_controller;
	}
	
	public function setController($controller)
	{
		if($controller===null)
		{
			$this->_controller = $this->defaultController;
		}else{
			$this->_controller = $controller;
		}
	}
	
	/**
	 * Get the action name
	 * @return string
	 */
	protected function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Set the action name
	 * @param string $action
	 */
	public function setAction($action)
	{
		if($action===null)
		{
			$this->_action = $this->action;
		}else{
			$this->_action = $action;
		}
	}

	/**
	 * Setting the user access rules, this method must be return an array.
	 * you can specify the user roles and set which actions can be accessed.
	 * @example 	
	 * return array(
	 *		'guest'=>array(
	 *				'allowActions'=>array('index','read'),
	 *		),
	 *		'member'=>array(
	 *				'allowActions'=>array('*')
	 *		),
	 *	);
	 * @return array
	 */
	public function auth()
	{
		return array();
	}

	/**
	 * Creates an instance of the renderer, and rendering a view.
	 * @param mixed $name
	 * @param mixed $data
	 * @param boolean $return
	 * @throws \RuntimeException
	 */
	public function render($name,$data=null,$return=false)
	{
		if(!$this->getRenderer() or !($this->renderer instanceof Renderer))
		{
			throw new \RuntimeException("Failed to instantiate renderer.");
		}
		
		if(is_array($name))
		{
			$this->getApplication()->listener->register('lightworx.render',array($this,'render'));
			foreach($name as $key=>$val)
			{
				$this->renderer->render($this,str_replace('.','/',$val),$data,$return);
			}
			return;
		}
		
		if(strpos($name,'/')===false)
		{
			$controllerExtension = \Lightworx::getApplication()->controllerExtension;
			$name = strtolower(str_replace($controllerExtension,'',$this->getControllerName())).'/'.$name;
		}
		
		$content = $this->renderer->render($this,$name,$data,$return);

		if($return and $content!==null)
		{
			$this->getResponse()->output($content);
		}
	}
	
	/**
	 * get renderer from configure
	 * @return boolean
	 */
	protected function getRenderer()
	{
		if($this->renderer===null)
		{
			$this->renderer = new Renderer;
		}
		
		if(is_object($this->renderer))
		{
			return $this->renderer;
		}
	}

	/**
	 * Run action of controller, if the specified action doesn't exists,
	 * that will be return an error message page, and send the http status as 404
	 * If the action is private or protected, That will be sent an http status 403 to client.
	 * @param string $action
	 */
	private function runAction($action)
	{
		$actionName = $this->getActionName($action);
		if(!is_callable(array($this,$actionName)))
		{
			$this->missingAction($action);
		}
		$this->$actionName();
	}
	
	/**
	 * check the action is exists
	 * @return boolean
	 */
	private function checkActionExists($action)
	{
		$methods = $this->getMethods();
		if(!in_array($action,$methods))
		{
			return false;
		}
		return true;
	}

	/**
	 * Throw an HttpException, when the action cannot be found
	 * @param String $action
	 * @throws HttpException
	 */
	protected function missingAction($action)
	{
		throw new HttpException(404,"Not found the action:".$action);
	}
	
	/**
	 * get all methods in current controller.
	 * @return array
	 */
	private function getMethods()
	{
		$methods = array();
		$controller = new \ReflectionClass($this->getController());

		foreach($controller->getMethods() as $key=>$method)
		{
			if($method->class==$this->getController())
			{
				$methods[] = $method->name;
			}
		}
		return $methods;
	}
	
	/**
	 * Redirect to a specified URL
	 * @param string $url
	 * @param boolean $terminate defaults to 'true', means stops to load the remaining contents.
	 * @param integer $statusCode HTTP status code, defaults to '302'
	 */
	public static function redirect($url,$terminate=true,$statusCode=302)
	{
		self::getRequest()->redirect($url,$terminate,$statusCode);
	}
	
	public static function renderView(){}
	
	/**
	 * Load specified model, and find one record by primary key.
	 * @param Lightworx\Queryworx\Base\Model
	 * @return Model
	 */
	protected function loadBaseModel(\Lightworx\Queryworx\Base\Model $model)
	{
		if($this->_model===null)
		{
			$pk = $model::model()->getPrimaryKeyName();
			if(isset($_GET[$pk]))
			{
				$this->_model=$model::model()->findByPk(array($pk=>$_GET[$pk]));
				
				if($this->_model===null)
				{
					throw new HttpException(404,"Cannot found the page: ".$_GET[$pk]);
				}
			}else{
				throw new HttpException(400,"The parameter id is invalid");
			}
		}
		return $this->_model;
	}
	
	/**
	 * Get request object
	 */
	public static function getRequest()
	{
		return \Lightworx::getApplication()->request;
	}
}