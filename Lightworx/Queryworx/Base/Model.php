<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Base;

use Lightworx\Foundation\Object;
use Lightworx\Component\Validator\ValidatorBuilder;

abstract class Model extends Object
{
	/**
	 * Choose a Database connector and setting the connection parameters 
	 * @var array
	 * @example:
	 *	array(
	 *		'connector'=>'PDOConnection',
	 *		'dsn'=>'mysql:dbname=dbname;host=127.0.0.1',
	 *		'username'=>'root',
	 *		'password'=>'yourpassword',
	 *		'options'=>array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
	 *	);
	 */
	protected $connectionParams;
	
	/**
	 * The attribute of active record
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * Sets a connection parameter array for the connectionParams
	 * @param array $connectionParams
	 */
	public function setConnectionParams(array $connectionParams)
	{
		$this->connectionParams = $connectionParams;
	}
	
	/**
	 * get property of this class, php magic method
	 * @param string $name
	 * @return mixed property
	 */
	public function __get($name)
	{
		if(array_key_exists($name,$this->_attributes))
		{
			return $this->_attributes[$name];
		}

		$methodName = 'get'.ucfirst($name);
		if(method_exists($this,$methodName))
		{
			return $this->$methodName();
		}
		
		if(property_exists($this,$name))
		{
			return $this->{$name};
		}
		return null;
	}
	
	/**
	 * set value for property of this class, php magic method
	 * @param string $name
	 * @param mixed $value
	 * @return void
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

		if(property_exists($this,$name))
		{
			$this->{$name} = $value;
		}
	}
	
	/**
	 * php magic method, get some a component or a widget
	 * @param string $method
	 * @param mixed $args
	 * @return 
	 */
	public function __call($method,$args)
	{
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
	 * Return all the attribute labels, 
	 * you may need to override this method.
	 * @return array
	 */
	public function attributeLabels()
	{
		return array();
	}
	
	/**
	 * Return the attribute label, if it is exist.
	 * @param string $name the attribute name
	 * @return string
	 */
	public function getAttributeLabel($name)
	{
		$attributeLabels = $this->attributeLabels();
		if(array_key_exists($name,$attributeLabels))
		{
			return $attributeLabels[$name];
		}
	}
	
	/**
	 * Return the model rules, you may need to override this method.
	 * @return array
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Validate the user input data
	 * @return boolean
	 */
	public function validate()
	{
		$validator = new ValidatorBuilder;
		if(method_exists($this,'rules'))
		{
			$validator->getValidators($this,$this->rules());
			if($this->getErrors()!==null)
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Set attributes
	 * @param array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach($attributes as $name=>$value)
		{
			if(property_exists($this,$name))
			{
				$this->{$name} = $value;
			}else{
				$this->_attributes[$name] = $value;
			}
		}
	}
}