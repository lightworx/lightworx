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

use Lightworx\Component\Caching\Cache;

class RedisCache extends Cache
{

	public $servers = array();

	/**
	 * The redis connection options
	 */
	public $options = array();
	
	public function initialize()
	{
		if(ClassLoader::hasNamespace('Predis')===false)
		{
			ClassLoader::registerNamespace('Predis',LIGHTWORX_PATH.'Vendors'.DS.'Storage'.DS.'predis'.DS.'lib'.DS);
		}
	}

	protected function connect()
	{
		$this->client = new \Predis\Client($this->servers,$this->options);
		if($this->client!==null)
		{
			return true;
		}
		return false;
	}

	protected function disconnect()
	{
		$this->client->quit();
	}
}