<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\File;

use Lightworx\Foundation\Object;

class Upload extends Object
{	
	/**
	 * @var integer Set the maximum of file size allowed
	 */
	public $fileSize;
	
	/**
	 * @var array Setting for allowed file mime type
	 */
	public $fileType = array('*');
	
	/**
	 * @var string or array Allowed file extension names, 
	 * a developer should use the Separator "," or "|" to separate extension name
	 * Note: the extension name must to starting with a dot.
	 * @example 
	 *			'fileExtension'=>array(".jpg",".png",".gif",".tar.gz"),
	 * @example
	 *			'fileExtension'=>".jpg|.png|.gif|.tar.gz",
	 */
	public $fileExtension;
	
	/**
	 * @var array Every attachment the extension of the filename is separated by a separator
	 */
	protected $extensionSeparator = '|';
	
	/**
	 * @var string Set the uploading file store path;
	 */
	public $uploadPath;
	
	/**
	 * @var string Set web browser visit path,  if the uploaded file store in directory {@link PUBLIC_PATH}, 
	 * that will be return a web file path.
	 */
	protected $visitFile;
	
	/**
	 * @var mixed A developer can costomize hash path by that property.
	 * @example user_func_call($hashPathCallback);
	 */
	public $hashPathCallback = false;
	
	/**
	 * 
	 * @var mixed A developer can costomize rename method
	 */
	public $renameCallback = false;
	
	/**
	 * setting the upload path whether was created.
	 * @var boolean default false
	 */
	static protected $uploadPathCreated = false;
	
	/**
	 * @var integer when uploading multiple files, appending a sequence for the file name.
	 */
	private static $sequence = 0;
	
	/**
	 * @var array $_FILES using for assign to the property $file
	 */
	public $file = null;
	
	public $DirectoryPermission = 0755;
	
	/**
	 * Formating the file extension configure parameter
	 * @return void
	 */
	public function formatFileExtensionConfig()
	{
		if(is_string($this->fileExtension))
		{
			$fileExtension = strtolower($this->fileExtension);
		}
	
		if(is_array($this->fileExtension))
		{
			$fileExtension = strtolower(implode($this->extensionSeparator,$this->fileExtension));
		}
		
		$this->fileExtension = explode($this->extensionSeparator,$fileExtension);
	}
	
	/**
	 * starting upload files
	 */
	public function uploadFile()
	{
		if($this->validateUploadFile() and $this->beforeUpload())
		{
			$dest = $this->getDestinationFile();
			if(is_uploaded_file($this->file['tmp_name']) and move_uploaded_file($this->file['tmp_name'],$dest))
			{
				$this->destFile = $dest;
				$this->setVisitFile($this->destFile);
			}else{
				$this->addError("Moving uploaded file failed.");
			}
			$this->afterUpload();
		}
		\Lightworx::getApplication()->setNotify($this,false);
	}
	
	/**
	 * Set the file visit path, if the uploaded file store in directory {@link PUBLIC_PATH}, 
	 * that will return a web file path.
	 * @param string $destFile
	 */
	public function setVisitFile($destFile)
	{
		if(strpos($destFile,PUBLIC_PATH)===0)
		{
			$this->visitFile = str_replace(PUBLIC_PATH,'/',$destFile);
		}else{
			$this->visitFile = null;
		}
	}
	
	/**
	 * Creates a upload directory for the file store
	 */
	public function createUploadPath()
	{
		if(self::$uploadPathCreated)
		{
			return true;
		}
		
		if(is_callable($this->hashPathCallback))
		{
			$this->uploadPath .= $this->hashPathCallback;
		}else{
			$this->uploadPath .= date('Y/m/d/',time()).(time() % 200).'/';
		}
		
		if(is_dir($this->uploadPath)===false)
		{
			if(mkdir($this->uploadPath,$this->DirectoryPermission,true))
			{
				self::$uploadPathCreated = true;
				return true;
			}
		}else{
			self::$uploadPathCreated = true;
			return true;	
		}
		
		return false;
	}
	
	/**
	 * get the extension name of the uploaded file.
	 * @return string
	 */
	public function getFileExtension()
	{
		if(strpos($this->file['name'],'.')===false)
		{
			$this->addError("The upload file have no extension name");
			return false;
		}
		$this->formatFileExtensionConfig();
		$extension = '';
		$filenames = array_reverse(explode('.',strtolower($this->file['name'])));
		foreach($filenames as $key=>$name)
		{
			$extension = '.'.$name.$extension;
			if(in_array($extension,$this->fileExtension))
			{
				return $extension;
			}
		}
		return false;
	}
	
	/**
	 * Validate uploaded file
	 */
	public function validateUploadFile()
	{
		if($this->file['error']>0 and is_int($this->file['error']))
		{
			$this->addError($this->file['error']);
			return false;
		}
		
		if($this->validateFileSize()===false)
		{
			$this->addError('The uploaded file size must be less than {fileSize}',array('{fileSize}'=>FileInfo::bytesToSize($this->fileSize)));
			return false;
		}
		
		if($this->validateFileExtension()===false)
		{
			$this->addError('The upload file must be {allowFileTypes} one of them.',
							array('{allowFileTypes}'=>implode(', ',$this->fileExtension)));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate the upload file size
	 * @return boolean
	 */
	public function validateFileSize()
	{
		is_string($this->fileSize) ? $this->fileSize = FileInfo::sizeToBytes($this->fileSize) : "";	
		if($this->fileSize>=$this->file['size'])
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Validate file extension whether be allowed
	 * @return boolean
	 */
	public function validateFileExtension()
	{
		if($this->getFileExtension()===false)
		{
			return false;
		}
		return true;
	}

	/**
	 * Rename uploaded file name
	 * @return string
	 */
	public function renameUploadFile()
	{
		if($this->renameCallback!==false and is_callable($this->renameCallback))
		{
			$name = $this->renameCallback;
		}else{
			list($msec,$sec) = explode(" ",microtime());
			$name = sprintf("%10d%06d%s",$sec,substr($msec,2,6).self::$sequence++,$this->getFileExtension());
		}
		return $name;
	}
	
	/**
	 * Get destination file, contained path and file name
	 * @throws \RuntimeException
	 * @return string
	 */
	public function getDestinationFile()
	{
		if($this->createUploadPath())
		{
			return $this->uploadPath.$this->renameUploadFile();
		}
		throw new \RuntimeException("Cannot create directory at ".$this->uploadPath);
	}

	/**
	 * before to upload file,first execute that method,
	 * you can extend current class and override this method
	 * @return true
	 */
	public function beforeUpload()
	{
		return true;
	}
	
	/**
	 * When finished upload file,  that method will be execute
	 */
	public function afterUpload(){}
}