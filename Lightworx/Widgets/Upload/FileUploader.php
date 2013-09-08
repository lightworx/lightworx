<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Upload;

use Lightworx\Foundation\Widget;

class FileUploader extends Widget
{
	public $uploadObject = '.fileUpload';
	public $uploaderConfig = array();
	
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Uploader/FileUploader/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('FileUploader',$config);
		
		// $this->attachPackageCssFile("FileUploader",'reset.css');
		$this->attachPackageCssFile("FileUploader",'fileUploader.css');
		$this->attachPackageCssFile("FileUploader",'ui-lightness/jquery-ui-1.8.14.custom.css');
		
		$this->attachPackageScriptFile("FileUploader",'jquery-ui-1.8.14.custom.min.js');
		$this->attachPackageScriptFile("FileUploader",'jquery.fileUploader.js');
	}
	
	public function run()
	{
		$this->addJqueryCode('jQuery(function($){
			$("'.$this->uploadObject.'").fileUploader('.$this->uploaderConfig().');
		});');
	}
	
	protected function uploaderConfig()
	{
		$config = array();
		foreach($this->uploaderConfig as $property=>$value)
		{
			$config[] = '"'.$property.'":"'.$value.'"';
		}
		
		if($config===array())
		{
			return;
		}
		
		return "{".implode(",\n",$config)."}";
	}
}