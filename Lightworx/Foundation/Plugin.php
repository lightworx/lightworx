<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Foundation;

use Lightworx\Helper\Html;
use Lightworx\Foundation\Event;
use Lightworx\Foundation\ErrorHandler;
use Lightworx\Component\HttpFoundation\AssetManager;

abstract class Plugin
{
	private $_currentObject;
	private $_currentEventName;
	
	abstract public function register();
	
	public function __construct(){}
	
	
	static public function attachEvent($name,$callable)
	{
		Event::attach($name,$callable);
	}
	
	static public function detachEvent($name,$priority=null)
	{
		Event::detach($name,$priority);
	}
	
	final public function getCurrentObject()
	{
		return $this->_currentObject;
	}
	
	final public function getCurrentEventName()
	{
		return $this->_currentEventName;
	}
}