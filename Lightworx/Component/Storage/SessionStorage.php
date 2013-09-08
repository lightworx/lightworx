<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Storage;


class SessionStorage extends Storage
{
	/**
	 * This is a key/value pair array, representing the a session key and a value of this session key.
	 * @var array
	 */
	public $sessions = array();

	public $name;
	public $value;

	/**
	 * Setting the proerpties to sessions
	 * @param array $properties
	 */
	public function setProperties(array $properties)
	{
		$this->sessions = $properties;
	}
	
	/**
	 * automatically call method assignment value or get value for specifying property.
	 * @param string $method
	 * @param mixed $value
	 * @throws \RuntimeException
	 * @return void|unknown
	 */
	public function __call($method,$value)
	{
		$property = lcfirst(substr($method,3));
		if(strpos($method,"get")===0 and property_exists($this,$property) and isset($_SESSION[$value[0]]))
		{
			return $_SESSION[$value[0]];
		}
		throw new \RuntimeException("Undefined the property:".$property);
	}
	
	/**
	 * Initialize the storage
	 */
	public function initialize()
	{
		session_start();
	}
	
	/**
	 * Save the session to PHP inside sessions
	 */
	public function save()
	{
		if($this->beforeSave())
		{
			if(is_array($this->value))
			{
				foreach($this->value as $key=>$session)
				{
					$_SESSION[$key] = $session;
				}
			}
			if(is_string($this->value) and is_string($this->name))
			{
				$_SESSION[$this->name] = $this->value;
			}
			$this->afterSave();
		}
	}
	
	public function getData()
	{
		$data = null;
		if($this->beforeRead())
		{
			if(is_string($this->name) and isset($_SESSION[$this->name]))
			{
				$data = $_SESSION[$this->name];
			}
			if(is_array($this->name))
			{
				foreach($this->name as $sessionKey)
				{
					if(isset($_SESSION[$sessionKey]))
					{
						$data[] = $_SESSION[$sessionKey];
					}
				}
			}
			$this->afterRead();
		}
		return $data;
	}
	
	/**
	 * Checking a session whether exists
	 * @param string $name
	 * @return boolean
	 */
	public function isExists($name)
	{
		return isset($_SESSION[$name]);
	}
	
	/**
	 * Logout current sessions
	 */
	public function logout()
	{
		session_unset();
		session_destroy();
	}
	
	/**
	 * alias name of the logout
	 */
	public function remove()
	{
		$this->logout();
	}
}