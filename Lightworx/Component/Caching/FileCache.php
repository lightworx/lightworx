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
use Lightworx\Component\Storage\FileStorage;

class FileCache extends Cache
{
	public $cachePath;
	public $cacheFileSuffix = '.bin';
	public $fileStorage;
	
	public function initialize()
	{
		$this->fileStorage = new FileStorage;
		$this->cachePath = $this->fileStorage->storagePath = \Lightworx::getApplication()->getCachePath();
	}
	
	public function get($id)
	{
		$cacheFile = $this->cachePath.$id.$this->cacheFileSuffix;
		$this->fileStorage->storageFileName = $id.$this->cacheFileSuffix;
		if(!file_exists($cacheFile))
		{
			return false;
		}
		$value = $this->fileStorage->getData();
		if(@filemtime($cacheFile)<time())
		{
			unlink($cacheFile);
		}
		return $value;
	}
	
	public function set($id,$value,$expire)
	{
		$cacheFile = $this->cachePath.$id.$this->cacheFileSuffix;
		$this->fileStorage->storageFileName = $id.$this->cacheFileSuffix;
		$this->fileStorage->mode = 'wb';
		$this->fileStorage->data = $value;
		
		$expire += time();
		
		$this->fileStorage->save();
		@chmod($cacheFile,0777);
		return @touch($cacheFile,$expire);
	}
	
	/**
	 * Delete cache by the specified cache id
	 * @param string $id
	 * @return boolean if delete cache file is success then return true, 
	 *                 fail return false, if the file does not exist, return true.
	 */
	public function delete($id)
	{
		$cacheFile = $this->cachePath.$id.$this->cacheFileSuffix;
		if(file_exists($cacheFile))
		{
			return @unlink($cacheFile);
		}
		return true;
	}
	
	/**
	 * Clear all the cache.
	 */
	public function flush()
	{
		$cacheFiles = glob($this->cachePath.'*'.$this->cacheFileSuffix);
		foreach($cacheFiles as $cacheFile)
		{
			@unlink($cacheFile);
		}
	}
}