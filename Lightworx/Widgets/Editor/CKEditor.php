<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Editor;

use Lightworx\Foundation\Widget;

class CKEditor extends Widget
{
	/**
	 * The editor id
	 */
	public $editorId;
	
	/**
	 * The editor configuration.
	 * you can specifying various parameter for the editor.
	 * @var array
	 */
	public $editorProperties = array();
	
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Editor/ckeditor/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('ckeditor',$config);
		$this->attachPackageScriptFile("ckeditor",'ckeditor.js');
		$this->attachPackageScriptFile("ckeditor",'samples/assets/uilanguages/languages.js');
	}
	
	public function run()
	{
		if(!isset($this->editorProperties['language']))
		{
			$language = str_replace('_','-',\Lightworx::getApplication()->language);
			$this->editorProperties['language'] = $language;
		}
		
		$scriptCode = "//<![CDATA[
						CKEDITOR.replace( '".$this->editorId."',
						{
							".$this->getJQueryPluginProperties($this->editorProperties)."
						});
					//]]>";
		$this->addScriptCode($scriptCode);
	}
}