<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Caching;

abstract class Cache
{
	public function __construct()
	{
		$this->initialize();
	}
	
	public function setProperties(array $properties)
	{
		foreach($properties as $property=>$value)
		{
			$this->{$property} = $value;
		}
	}
	
	abstract public function initialize();
	
	abstract public function get($id);
	
	abstract public function set($id,$value,$expire);
	
	abstract public function delete($id);
	
	abstract public function flush();
}