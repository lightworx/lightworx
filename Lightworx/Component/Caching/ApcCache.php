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

class ApcCache extends Cache
{

	public function initialize()
	{
		if(!extension_loaded('apc'))
		{
			throw new \RuntimeException('The server does not support extension APC');
		}
	}

	public function get($id)
	{
		return apc_fetch($id);
	}

	public function getValues($ids)
	{
		return apc_fetch($ids);
	}

	public function set($id,$value,$expire)
	{
		return apc_store($id,$value,$expire);
	}

	public function add($id,$value,$expire)
	{
		return apc_add($id,$value,$expire);
	}

	public function delete($id)
	{
		return apc_delete($id);
	}

	public function flush()
	{
		return apc_clear_cache('user');
	}
}