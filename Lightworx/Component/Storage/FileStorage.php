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

class FileStorage extends Storage
{
	
	/**
	 * Set the storage file of the directory
	 * @var string
	 */
	public $storagePath;
	
	/**
	 * Set name for the storage file name
	 * @var string
	 */
	public $storageFileName;
	
	/**
	 * Storage data
	 * @var string
	 */
	public $data;
	
	/**
	 * Contained path and file name
	 * @var string
	 */
	protected $storageFile;
	
	/**
	 * The permission of the directory.
	 * @var integer
	 */
	public $permissions = 0777;
	
	/**
	 * The mode parameter specifies the type of access you require to the stream.
	 * @var string
	 * @see http://www.php.net/manual/en/function.fopen.php
	 */
	public $mode = 'a+b';
	
	/**
	 * When the directory does not exist, and the 'createDir' is true, it will automatically create the directory.
	 * @var boolean defaults to true
	 */
	public $createDir = true;
	
	/**
	 * Return a string contains file name and storage path, 
	 * if the path does not exist, then that will come to create the directories using the recursive way,
	 * and make sure the path is writable.
	 * @return string
	 */
	protected function getStorageFile()
	{
		$this->createDirectory();
		return $this->storagePath.$this->storageFileName;
	}
	
	/**
	 * Creates a directory for the storage file.
	 * @throws \RuntimeException
	 */
	public function createDirectory()
	{
		if(is_dir($this->storagePath) and is_writable($this->storagePath)===false)
		{
			chmod($this->storagePath,$this->permissions);
		}
		
		if(is_dir($this->storagePath) and is_writable($this->storagePath))
		{
			return true;
		}
		
		if(!is_dir($this->storagePath) and $this->createDir===true and mkdir($this->storagePath,$this->permissions,true))
		{
			return true;
		}
		
		throw new \RuntimeException("Cannot to create the storage path:".$this->storagePath);
	}
	
	/**
	 * Save data to specifying destination file.
	 */
	public function save()
	{
		if($this->beforeSave())
		{
			$dest = $this->getStorageFile();
			
			if(file_exists($dest) and is_writable($dest)===false)
			{
				@chmod($dest,0777);
			}
			
			$fp = fopen($dest,$this->mode);
			fwrite($fp,$this->data);
			fclose($fp);
			$this->afterSave();
		}
	}
	
	/**
	 * Get data from file storage, if the file not exists, that will be return an empty string.
	 * return string
	 */
	public function getData()
	{
		$data = '';
		if($this->beforeRead())
		{
			$filename = $this->getStorageFile();
			
			if($this->enablePersistentData===true and isset(self::$persistentData[$filename]))
			{
				return self::$persistentData[$filename];
			}		
			
			if(!file_exists($filename) or filesize($filename)===0)
			{
				return '';
			}
			
			$fp = fopen($filename,'rb');
			self::$persistentData[$filename] = $data = fread($fp,filesize($filename));
			fclose($fp);
			$this->afterRead();
		}
		return $data;
	}
}