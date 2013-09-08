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

class Uploader extends Upload
{
	/**
	 * Set the number of the upload file.
	 * @var mexid
	 */
	public $uploadNum = 1;
	
	/**
	 * @var boolean When uploading multiple files and occurred an error,
	 * and if this property is 'true', that will be skip the error and keep uploading other file(s).
	 * defaults to true, means skip errors.
	 * and get the error message should be from the component notification.
	 */
	public $skipError = true;
	
	/**
	 * The form name of uploading file.
	 * @var string
	 */
	public $formName;
	
	/**
	 * This property whether to set the submitted file can be empty or not.
	 * @var boolean defaults to true, means allow empty.
	 */
	public $allowEmpty = true;
	
	
	public function __construct(){}
	
	public function execute()
	{
		if(\Lightworx::getApplication()->request->hasFileUpload()===false)
		{
			return;
		}
		$this->toUploadFiles();
	}
	
	/**
	 * Starting upload files from an iterator
	 * @throws \RuntimeException
	 */
	public function toUploadFiles()
	{
		if(!isset($_FILES[$this->formName]))
		{
			throw new \RuntimeException("The form name ".$this->formName." cannot be found.");
		}
		
		$files = $this->getSingleFileToArray();
		for($i=0;$i<(int)$this->uploadNum and isset($files[$i]);$i++)
		{
			$this->setFile($files[$i]);
			$this->uploadFile();
		}
	}
	
	/**
	 * Get each uploaded file from $_FILES, and set to a new array.
	 * @throws \RuntimeException
	 */
	public function getSingleFileToArray()
	{
		$file = $files = array();
		if(is_array($_FILES[$this->formName]['name']))
		{
			foreach($_FILES[$this->formName]['name'] as $key=>$val)
			{
				$file['name'] = $_FILES[$this->formName]['name'][$key];
				$file['type'] = $_FILES[$this->formName]['type'][$key];
				$file['tmp_name'] = $_FILES[$this->formName]['tmp_name'][$key];
				$file['error'] = $_FILES[$this->formName]['error'][$key];
				$file['size'] = $_FILES[$this->formName]['size'][$key];
				$files[] = $file;
			}
			return $files;
		}
		
		if(is_string($_FILES[$this->formName]['name']))
		{
			return array($_FILES[$this->formName]);
		}
		throw new \RuntimeException("The variable \$_FILES is incorrect.");
	}
}