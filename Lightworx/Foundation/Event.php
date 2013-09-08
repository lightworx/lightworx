<?php
/**
 * This file is part of the Lightworx framework
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Event.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

class Event
{
	static private $observers = array();
	static public $nameCaseSensitive = false;
	
	static public function attach($name,$callable,$priority=0)
	{
		$name = self::ensureName($name);
		if(isset(self::$observers[$name]) and isset(self::$observers[$name][$priority]))
		{
			$priority = count(self::$observers[$name])+1;
		}
		self::$observers[$name][$priority] = $callable;
	}
	
	static public function detach($name,$priority=null)
	{
		$name = self::ensureName($name);
		if(isset(self::$observers[$name]))
		{
			if($priority!==null and isset(self::$observers[$name][$priority]))
			{
				unset(self::$observers[$name][$priority]);
			}else{
				unset(self::$observers[$name]);
			}
		}
	}
	
	static public function notify($name,&$object)
	{
		$name = self::ensureName($name);
		if(isset(self::$observers[$name]) and is_array(self::$observers[$name]))
		{
			rsort(self::$observers[$name]);
			foreach(self::$observers[$name] as $key=>$observer)
			{
				call_user_func($observer,$object);
			}
		}
	}
	
	static public function ensureName($name)
	{
		if(self::$nameCaseSensitive===false)
		{
			return strtolower($name);
		}
		return $name;
	}


	static public function dumpObservers()
	{
		return self::$observers;
	}
}