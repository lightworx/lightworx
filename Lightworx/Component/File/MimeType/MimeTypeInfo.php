<?php

namespace Lightworx\Component\File\MimeType;

use Lightworx\Component\File\MimeType\FileBinaryMimeTypeInfo;
use Lightworx\Component\File\MimeType\ContentTypeMimeTypeInfo;
use Lightworx\Component\File\MimeType\FileInfoMimeTypeInfo;

class MimeTypeInfo
{
	
	public $file;
	public $path;
	public $finders;
	public static $instance;
	
	public static function getInstance($file)
	{
		if((self::$instance instanceof MimeTypeInfo)===false)
		{
			self::$instance = new self($file);
		}
		return self::$instance;
	}
	
	private function __construct($file)
	{
		$this->file = $file;
		$this->path = realpath($file);
		
		$this->finders = array(new FileBinaryMimeTypeInfo,new ContentTypeMimeTypeInfo,new FileInfoMimeTypeInfo);
	}
	
	public function checkFile($file)
	{
		if(!is_readable($file))
		{
			throw new RuntimeException("can not reading the file ".$file);
		}
		return true;
	}
	
	public function checkPath($path)
	{
		if(!is_readable($path))
		{
			throw new RuntimeException("Can not access the directory: ".$path);
		}
		return true;
	}
	
	public function getFile()
	{
		return $this->file;
	}
	
	public function getPath()
	{
		return $this->path;
	}

	public function getMimeType()
	{
		if($this->checkFile($this->file) and $this->checkPath($this->path) and is_array($this->finders))
		{
			foreach($this->finders as $finder)
			{
				$mimetype = $finder->getMimeType($this->file);
				if(!is_null($mimetype))
				{
					return $mimetype;
				}
			}
		}
		return 'application/octet-stream';
	}
	
	public function __toString()
	{
		return $this->getMimeType();
	}
}