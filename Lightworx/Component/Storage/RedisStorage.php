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

use Lightworx\Foundation\ClassLoader;

class RedisStorage extends Storage
{
	public $servers = array();

	/**
	 * The redis connection options
	 */
	public $options = array();

	protected $client;

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

	public function beforeSave()
	{
		if(is_array($this->data) and isset($this->servers[0]) and is_array($this->servers[0]))
		{
			$this->servers = $this->servers[0];
		}
		return true;
	}

	public function beforeRead()
	{
		if(is_array($this->key) and isset($this->servers[0]) and is_array($this->servers[0]))
		{
			$this->servers = $this->servers[0];
		}
		return true;
	}

	public function save()
	{
		if($this->beforeSave() and $this->connect())
		{
			if(is_array($this->data))
			{
				$this->client->mset($this->data);
			}
			if(is_string($this->data))
			{
				$this->client->set($this->key,$this->data);
			}
			$this->disconnect();
			$this->afterSave();
		}
	}
	
	public function getData()
	{
		$data = '';
		if($this->beforeRead() and $this->connect())
		{
			if(is_array($this->key))
			{
				$data = $this->client->mget($this->key);
			}
			if(is_string($this->key))
			{
				$data = $this->client->get($this->key);
			}
			$this->disconnect();
			$this->afterRead();
		}
		return $data;
	}
}