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

class DummyCache extends Cache
{
	public function initialize(){}

	public function get($id)
	{
		return null;
	}
	
	public function getValues($ids)
	{
		return null;
	}
	
	public function set($id,$value,$expire){}
	
	public function delete($id)
	{
		return true;
	}
	
	public function flushValue(){}
}