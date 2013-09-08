<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Renderer;

use Lightworx\Foundation\Object;
use Lightworx\Component\Caching\Cache;
use Lightworx\Component\Routing\Router;
use Lightworx\Controller\Controller;
use Lightworx\Foundation\Application;
use Lightworx\Foundation\Module;
use Lightworx\Component\Translation\ViewTranslator;

class BaseRenderer extends Object
{
	private $_widgetStack = array();
	private $_viewPath;
	private $_controller;
	private $_themePath;
	
	protected $_cache;
	
	public static $router;
	
	public function __get($name)
	{
		if(property_exists($this,$name))
		{
			return $this->name;
		}
	}
	
	public function __set($name,$value)
	{
		$this->{$name} = $value;
	}
	
	public function getThemePath()
	{
		return $this->_themePath;
	}
	
	public function setThemePath($themePath)
	{
		$this->_themePath = $themePath;
	}
	
	public function getViewPath()
	{
		return $this->_viewPath;
	}
	
	public function setViewPath($viewPath)
	{
		$this->_viewPath = $viewPath;
	}
	
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * Get the controller name, defaults without controller suffix.
	 * @param $suffix defaults to false
	 */
	public function getControllerName($suffix=false)
	{
		if($suffix)
		{
			return \Lightworx::getApplication()->controllerName;
		}
		return \Lightworx::getApplication()->router->controller;
	}
	
	public function getActionName()
	{
		return \Lightworx::getApplication()->router->action;
	}

	public function setController(Controller $controller)
	{
		$this->_controller = $controller;
	}
	
	/**
	 * Starting create a widget
	 */
	public function beginWidget($className,$properties=array())
	{
		$widget=$this->createWidget($className,$properties);
		$this->_widgetStack[]=$widget;
		return $widget;
	}
	
	/**
	 * End of the widget and running it.
	 */
	public function endWidget()
	{
		$widget = array_pop($this->_widgetStack);
		if($widget!==null)
		{
			$widget->run();
			return $widget;
		}
		throw new \RuntimeException("Cannot found the widget");
	}
	
	/**
	 * Gets a widget instance
	 * @param string $widgetName
	 * @param mixed you may need to assign the parameter of the construct
	 * @return widget
	 */
	public function getWidget($widgetName)
	{
		if(strstr($widgetName,'.')!==false)
		{
			$widgetName = str_replace(".","\\",$widgetName);
		}
		
		if(class_exists($widgetName))
		{
			$config = func_get_args();
			unset($config[0]);
			$class=new \ReflectionClass($widgetName);
			$widget=call_user_func_array(array($class,'newInstance'),$config);
			return $widget;
		}else{
			throw new \RuntimeException("Cannot found the widget:".$id);
		}
	}
	
	/**
	 * Creates a widget and initialize the widget.
	 * @param mixed $widgetName
	 * @param array $properties the property of the widget
	 * @return widget
	 */
	public function createWidget($widget,array $properties=array())
	{
		if(is_string($widget))
		{
			$widgetName = $widget;
			if(strpos($widget,'.')!==false)
			{
				$widgetName = str_replace(".","\\",$widget);
			}
			
			if(class_exists($widgetName))
			{
				$widget = new $widgetName;
			}else{
				throw new \RuntimeException("Cannot found the widget:".$widgetName);
			}
		}
		
		try{
			foreach($properties as $property=>$value)
			{
				$widget->$property = $value;
			}
			$widget->init();
			return $widget;
		}catch(\Exception $e){
			throw new \RuntimeException($e->getMessage());
		}
	}
	
	/**
	 * Creates a widget and running it.
	 * @param string $widgetName
	 * @param array $properties
	 * @param boolean $captureOutput default value is false
	 */
	public function widget($widgetName,$properties=array(),$captureOutput=false)
	{
		if($captureOutput)
		{
			ob_start();
			ob_implicit_flush(false);
			$widget=$this->createWidget($widgetName,$properties);
			$widget->run();
			return ob_get_clean();
		}else{
			$widget=$this->createWidget($widgetName,$properties);
			$widget->run();
			return $widget;
		}
	}
	
	/**
	 * Set view language file
	 * @param string $name
	 * @param string $extension the language file extension, defaults to '.php'
	 */
	public function setViewMessageFile($name,$extension='.php')
	{
		$this->viewMessageName = $name.$extension;
	}
	
	/**
	 * Get the view language file
	 * @return string
	 */
	public function getViewMessageFile()
	{
		if(\Lightworx::getApplication()->viewMessagePath!==null)
		{
			return $this->viewMessageName;
		}
	}
	
	public function getViewTranslator($language='')
	{
		return new ViewTranslator($this->getViewMessageFile(),$language);
	}
	
	public function __($template,array $placeholder=array())
	{
		$translator = $this->getViewTranslator();
		return $translator->__($template,$placeholder);
	}
	
	/**
	 * Starting to capture a part of view content to the output buffer.
	 * @param string $view
	 * @param boolean $appView if using this method in a module, 
	 *               and want to display the content in a application view,
	 *               you should to set this argument is true, defaults to false.
	 */
	public function beginContent($view)
	{
		$this->view = $view;
		ob_start();
		ob_implicit_flush(false);
	}
	
	/**
	 * capture the output buffer, and render on a view.
	 * @param boolean $return whether return the value or not.
	 */
	public function endContent($return=false)
	{
		$content = ob_get_clean();
		$controller = $this->getController();
		$controller->layout = false;
		$content = $this->render($controller,$this->view,$content,$return);
		if($return)
		{
			return $content;
		}
	}
	
	public static function getRouter()
	{
		if(self::$router===null)
		{
			self::$router = new Router;
		}
		return self::$router;
	}
	
	public function createUrl($rule,array $params=array())
	{
		return self::getRouter()->createAbsoluteUrl($rule,$params);
	}
	
	public function beginCache($cacheDriver,$cacheId)
	{
		$this->_cache = \Lightworx::getApplication()->getComponent($cacheDriver);

		if(!($this->_cache instanceof Cache))
		{
			throw new \RuntimeException("The ".$cacheDriver." is a invalid cache component.");
		}

		$content = $this->_cache->get($cacheId);
		if($content===false or $content=='' or $content===null)
		{
			ob_start();
			ob_implicit_flush(false);
		}else{
			$this->_cache = null;
			echo $content;
			return false;
		}
		return true;
	}
	
	public function endCache($cacheId,$expire=3600)
	{
		if($this->_cache===null)
		{
			return;
		}
		$content = ob_get_clean();
		$this->_cache->set($cacheId,$content,$expire);
		echo $content;
	}
}