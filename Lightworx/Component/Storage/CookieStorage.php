<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Storage;


class CookieStorage extends Storage
{
	public $name;
	public $value;
	public $expire = 0;
	public $path = '/';
	public $domain;
	public $secure = false;
	public $httpOnly = false;
	
	
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
		if(strpos($method,"get")===0 and property_exists($this,$property) and isset($_COOKIE[$value[0]]))
		{
			return $_COOKIE[$value[0]];
		}
		throw new \RuntimeException("Undefined the property:".$property);
	}
	
	/**
	 * Initialize this storage
	 */
	public function initialize()
	{
		if(isset($_SERVER['HTTPS']))
		{
			$this->secure = true;
		}
		
		if(isset($_SERVER['HTTP_HOST']))
		{
			$this->domain = $_SERVER['HTTP_HOST'];
		}
	}
	
	/**
	 * Saves a cookie to browser
	 * @return boolean
	 */
	public function save()
	{
		if($this->beforeSave())
		{
			if(setcookie($this->name,$this->value,$this->expire,$this->path,$this->domain,$this->secure,$this->httpOnly))
			{
				$this->afterSave();
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Return the specify cookie, if it is exist.
	 */
	public function getData()
	{
		$data = null;
		if($this->beforeRead())
		{
			if(is_string($this->name) and isset($_COOKIE[$this->name]))
			{
				$data = $_COOKIE[$this->name];
			}
			if(is_array($this->name))
			{
				foreach($this->name as $name)
				{
					if(isset($_COOKIE[$name]))
					{
						$data[] = $_COOKIE[$name];
					}
				}
			}
			$this->afterRead();
		}
		return $data;
	}
	
	/**
	 * Checking a cookie whether exists.
	 * @param string $name
	 * @return boolean
	 */
	public function isExists($name)
	{
		return isset($_COOKIE[$name]);
	}
}