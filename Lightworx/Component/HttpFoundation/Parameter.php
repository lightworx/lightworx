<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Parameter.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\HttpFoundation;

class Parameter implements \ArrayAccess
{
	protected $params;
	
	public function __construct(array $param=array())
	{
		$this->params = $param;
	}
	
	public function get($name,$default=null)
	{
        return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
	}
	
	public function add(array $params=array())
	{
		$this->params = array_replace($this->params,$params);
	}
	
	public function all()
	{
		return $this->params;
	}
	
	public function set($key,$value)
	{
		$this->params[$key] = $value;
	}
	
	
	public function has($key)
	{
		return array_key_exists($key,$this->params);
	}

    public function delete($key)
    {
        unset($this->params[$key]);
    }


    public function getAlpha($key, $default = '')
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }


    public function getAlnum($key, $default = '')
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }
	
	public function getInt($key)
	{
		return (int)$this->get($key);
	}
	
	public function getDigits($key, $default = '')
    {
        return preg_replace('/[^[:digit:]]/', '', $this->get($key, $default));
    }

	public function offsetExists($name)
	{
		return array_key_exists($name,$this->params);
	}
	
	public function offsetGet($name)
	{
		if(!array_key_exists($name,$this->params))
		{
			throw new \RuntimeException("can not found index ".$name);
		}
		return $this->params[$name];
	}
	
	public function offsetSet($name,$value)
	{
		$this->params[$name] = $value;
	}
	
	public function offsetUnset($name)
	{
		unset($this->params[$name]);
	}
}