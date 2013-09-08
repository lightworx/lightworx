<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Caching;

use Lightworx\Component\Caching\Cache;

class MemcachedCache extends Cache
{
	public $servers = array(
		array(
			'host'=>'127.0.0.1',
			'port'=>11211,
			'persistent'=>true,
			'weight'=>1,
			'timeout'=>5,
			'retryInterval'=>15,
			'status'=>true
		),
	);
	
	/**
	 * The memcache instance.
	 */
	private $_memcache;
	
	public function initialize()
	{
		if(!extension_loaded('memcached'))
		{
			throw new \RuntimeException("The server does not support extension memcached");
		}
	}
	
	public function getCache()
	{
		if($this->_memcache===null)
		{
			$this->_memcache = new \Memcached;
			$this->createConnection();
		}
		return $this->_memcache;
	}
	
	/**
	 * Create a memcache server connection.
	 */
	public function createConnection()
	{
		if($this->_memcache===null)
		{
			throw new \RuntimeException("The memcache has no instance.");
		}
		
		$serverId = md5($this->name)%count($this->servers);
		$host = $this->servers[$serverId]['host'];
		$port = $this->servers[$serverId]['port'];
		$result = $this->_memcache->addServer($host,$port);

		// foreach($this->servers as $server)
		// {
		// 	$result = $this->_memcache->addServer($server['host'],$server['port']);
		// }
		
		if($result===false)
		{
			throw new \RuntimeException("The memcache connection is failed.");
		}
	}
	public $name;
	public function get($id)
	{
		$this->name = $id;
		return $this->getCache()->get($id);
	}
	
	public function set($id,$value,$expire=0)
	{
		$this->name = $id;
		if($expire>0)
		{
			$expire += time();
		}
		$this->getCache()->set($id,$value,$expire);
	}
	
	public function add($id,$value,$expire=0)
	{
		if($expire>0)
		{
			$expire += time();
		}
		$this->getCache()->add($id,$value,$expire);
	}
	
	/**
	 * Get a set value
	 * @param array $ids
	 */
	public function getValues($ids)
	{
		return $this->getCache()->get($ids);
	}
	
	/**
	 * Delete a value by specified key
	 * @param string $id
	 * @return boolean
	 */
	public function delete($id)
	{
		return $this->getCache()->delete($id);
	}
	
	/**
	 * Clear all values.
	 * @return boolean
	 */
	public function flush()
	{
		return $this->getCache()->flush();
	}
}