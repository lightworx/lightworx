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
	 * that will be return the action name contained action prefix and suffix
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
	 * Before to running that specify the action, 
	 * the 'beforeAction' will be perform, if that method return true,
	 * will be runing the specifying action and to perform 'afterAction'
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
	 * This method is invoked before runing an action.
	 * @param String $action
	 * @return boolean if the before{$action} method cannot be found, 
	 *                 that will be return true.
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
	 * When action run finished, this method will perform.
	 * you can override that method in your controller 
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
	 * Get action name
	 */
	protected function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Set action name
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
	 * you can specifying the user roles and set which actions can be accessed.
	 * @example 	
	 * return array(
	 *		'guest'=>array(
	 *				'actions'=>array('index','read'),
	 *		),
	 *		'member'=>array(
	 *				'actions'=>array('*')
	 *		),
	 *	);
	 * @return array
	 */
	public function auth()
	{
		return array();
	}

	/**
	 * Using the renderer to display view
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
	 * run action of controller, if action does't exists
	 * will to notifty a message to response, response send header information http code status 404
	 * if accessable property of action is private or protected, response will send http code status 403
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
	 * Throw an HttpException, when cannot found the action
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
	 * @param boolean $terminate defaults to 'true', means stop to loading the remaining contents.
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