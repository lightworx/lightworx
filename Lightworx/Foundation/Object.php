<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Object.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

use \Lightworx\Component\Translation\Translator;

class Object
{
	protected $notification;
	protected $events = array();
	
	private static $_events = array();
	private static $_errors;
	private $_components;
	private $_eventMethodPrefix = 'on';
	
	public $components = array();
	public $lightworxMessagePath;
	
	/**
	 * Get the specified property.
	 * If the method `get{property}` exists, then that will be invoked first.
	 * @param $name string
	 * @return property mixed
	 */
	public function __get($name)
	{
		if(!array_key_exists($name,get_object_vars($this)))
		{
			$methodName = 'get'.ucfirst($name);
			if(method_exists($this,$methodName))
			{
				return $this->$methodName();
			}else{
				throw new \RuntimeException("Undefined property: ".$name);
			}
		}
		return $this->$name;
	}
	
	/**
	 * Set a value for property of the class.
	 * @param $name string
	 * @param $value mixed
	 * @return 
	 */
	public function __set($name,$value)
	{
		if(!array_key_exists($name,get_object_vars($this)))
		{
			$methodName = 'set'.ucfirst($name);
			if(method_exists($this,$methodName))
			{
				$this->$methodName($value);
				return;
			}
		}
		$this->$name = $value;
	}
	
	/**
	 * The PHP magic method, gets a specified component.
	 * @param string $method
	 * @param mixed $args
	 * @return mixed
	 */
	public function __call($method,$args)
	{
		$eventMethod = lcfirst(substr($method,2));

		if(substr($method,0,2)==$this->_eventMethodPrefix and method_exists($this,$eventMethod))
		{
			if(isset(self::$_events[get_class($this)]))
			{
				return $this->executeEventMethod($eventMethod,$args,self::$_events[get_class($this)]);
			}else{
				return $this->executeEventMethod($eventMethod,$args);
			}
		}
		
		$properties = get_object_vars($this);
		$property   = lcfirst(substr($method,3));
		
		if(method_exists($this,$method)===false and array_key_exists($property,$properties))
		{
			if(strtolower(substr($method,0,3))=='get')
			{
				return $this->{$property};
			}
			
			if(strtolower(substr($method,0,3))=='set' and count($args)===1)
			{
				$this->{$property} = $args[0];
				return ;
			}
		}
		
		if(method_exists($this,$method)===false)
		{
			throw new \RuntimeException("The method:".$method." have no defined.");
		}
	}
	
	/**
	 * Executs an event method
	 * @param string $method
	 * @param mixed $args
	 * @param array $events
	 */
	public function executeEventMethod($method,$args,array $events = array())
	{
		if(isset($events[$method]) and isset($events[$method]['before']))
		{
			$this->executeBeforeEvents($events[$method]['before']);
		}
		
		if(!is_array($args))
		{
			$result = $this->{$method}($args);
		}else{
			$result = call_user_func_array(array($this,$method),$args);
		}
		
		if(isset($events[$method]) and isset($events[$method]['after']))
		{
			$this->executeAfterEvents($events[$method]['after']);
		}
		return $result;
	}
	
	/**
	 * Execute the specified method when the event triggers.
	 * @param array $events
	 */
	public function executeBeforeEvents(array $events)
	{
		foreach($events as $event)
		{
			if(is_string($event) and class_exists($event))
			{
				return new $event;
			}
			
			if(is_callable($event))
			{
				call_user_func($event);
			}
		}
	}
	
	/**
	 * Execute the specified method, when the event triggered.
	 * @param array $events
	 */
	public function executeAfterEvents(array $events)
	{
		foreach($events as $event)
		{
			if(is_callable($event))
			{
				call_user_func($event);
			}
		}
	}
	
	/**
	 * Add an event
	 * @param unknown_type $event
	 * @param unknown_type $object
	 * @param unknown_type $method
	 * @param unknown_type $on
	 */
	public function addEvent($event,$object,$method,$on)
	{
		self::$_events[$object][$method][$on][] = $event;
	}
	
	/**
	 *  set an event to object
	 *  @param object $object
	 *  @param string $name
	 *  @param array $arguments
	 */
	// public function setEvent($object,$name,$arguments = array())
	// {
	// 	$this->event[$name] = new Event($object,$name,$arguments);
	// 
	// 	$this->dispatcher->call($this->event[$name],$this->listener);
	// }
	
	/**
	 *  get specify event
	 *  @param string $name
	 */
	// public function getEvent($name)
	// {
	// 	if(in_array($name,$this->event))
	// 	{
	// 		return $this->event[$name];
	// 	}
	// 	return ;
	// }
	
	/**
	 * Get all notification from specify object.
	 * @param mixed $obj
	 * @return array
	 */
	public function getNotify($obj)
	{
		return $this->getNotification()->getNotify($obj);
	}
	
	/**
	 * Set an object save to object notification
	 * @param object $obj
	 * @param boolean $serialize defaults to false
	 */
	public function setNotify($obj,$serialize=false)
	{
		if(!is_object($obj))
		{
			throw new \RuntimeException("variable \$obj not an object");
		}
		$this->getNotification()->attach($obj,$serialize);
	}
	
	/**
	 * Gets a component and instance the specified component,
	 * return instance when the the component created.
	 * @param $name string
	 * @exception throw an runtime exception if cannot be found the parameters of the component
	 * @return mixed
	 */
	public function getComponent($name)
	{
		$config = array();
		
		if(isset(\Lightworx::getApplication()->components[$name]))
		{
			$config = \Lightworx::getApplication()->components[$name];
		}
		
		if(isset($this->_components[$name]) and isset($config['_reloadable_']) and $config['_reloadable_']===false)
		{
			return $this->_components[$name];
		}
		
		$args = func_get_args();
		array_shift($args);
		
		$component = \Lightworx::createComponent($name,$args);
		
		if(is_object($component))
		{
			return $component;
		}
		
		throw new \RuntimeException("Cannot be found the component parameters");
	}

	/**
	 * Set a component to the current object
	 */
	public function setComponent($object)
	{
		if($object!==null)
		{
			$this->_components[\Lightworx::formatObjectName($object)] = $object;
		}
	}
	
	/**
	 * Appending an error  message to the `$_errors` array
	 * @param string $text that should be a key of the messages array
	 * @param array $placeholders About the error related parameters
	 * @param string $key The rank of the error messages.
	 */
	public function addError($template,array $placeholders=array(),$key='')
	{
		$message = $this->getTranslator($this)->__($template,$placeholders);
		if($key=='')
		{
			self::$_errors[get_class($this)][] = $message;
		}else{
			self::$_errors[get_class($this)][$key] = $message;
		}
	}
	
	/**
	 * Get all errors from the current object.
	 * @return mixed If current object has no errors, that will be return null.
	 */
	public function getErrors($obj=null)
	{
		if($obj!==null and isset(self::$_errors[$obj]))
		{
			return self::$_errors[$obj];
		}
		if(isset(self::$_errors[get_class($this)]))
		{
			return self::$_errors[get_class($this)];
		}
		return null; 
	}
	
	/**
	 * Clear all errors of the current object.
	 * @param object $obj Defaults to `null`
	 * @return true
	 */
	protected function clearErrors($obj=null)
	{
		if($obj!==null and isset(self::$_errors[$obj]))
		{
			unset(self::$_errors[$obj]);
		}
		if(isset(self::$_errors[get_class($this)]))
		{
			unset(self::$_errors[get_class($this)]);
		}
		return true;
	}
	
	/**
	 * Get the object Translator, that will be supplied with an i18n environment.
	 * @param object $obj
	 * @return \Lightworx\Component\Translation\Translator
	 */
	public function getTranslator($obj='')
	{
		if(!is_object($obj))
		{
			$obj = $this;
		}
		$translator = new Translator($obj);
		return $translator;
	}
	
	/**
	 * Get the translation message, if the translation message cannot be found,
	 * that will be return the original message.
	 * @param string $string The original message template.
	 * @param array $placeholders The original message related arguments.
	 * @return string
	 */ 
	public function __($string,array $placeholders=array())
	{
		return $this->getTranslator($this)->__($string,$placeholders);
	}
}