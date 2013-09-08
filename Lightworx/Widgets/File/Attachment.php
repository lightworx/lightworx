<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\File;

use Lightworx\Foundation\Widget;
use Lightworx\Component\File\FileInfo;

class Attachment extends Widget
{
	public $file;
	
	public $template = "File Name:{fileName}\n File Hash:{fileHash}\n File Size:{fileSize}";
	
	/**
	 * whether enable the file size to formatting, defaults to true.
	 * @var boolean
	 */
	public $enableFileSizeFormat = true;
	
	/**
	 * specify the hash function, defaults to md5.
	 * @var string
	 */
	public $hashFunction = 'md5';
	
	public function renderFileName()
	{
		return basename($this->file);
	}
	
	public function renderFileHash()
	{
		$method = $this->hashFunction;
		return $method($this->file);
	}
	
	public function renderFileSize()
	{
		$fileSize = filesize($this->file);
		if($this->enableFileSizeFormat===null or $this->enableFileSizeFormat===false)
		{
			return $fileSize;
		}
		return FileInfo::bytesToSize($fileSize);
	}
	
	public function init()
	{
		if(is_string($this->hashFunction) and !function_exists($this->hashFunction))
		{
			throw new \RuntimeException("Can not found the hash function:".$this->hashFunction);
		}
		
		if(!file_exists($this->file))
		{
			throw new \RuntimeException("Can not found the file:".$this->file);
		}
	}
	
	public function run()
	{
		$contents = $filters = array();
		preg_match_all('~{(\w+)}~',$this->template,$templates);
		foreach($templates[1] as $key=>$placeholder)
		{
			$method = 'render'.ucfirst($placeholder);
			$contents[] = $this->$method();
			$this->template = str_replace($templates[0][$key],"%s",$this->template);
		}
		echo vsprintf($this->template,$contents);
	}
}