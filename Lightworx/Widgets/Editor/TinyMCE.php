<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Editor;

use Lightworx\Foundation\Widget;

class TinyMCE extends Widget
{
	public $editorId;
	public $editorProperties = array("mode"=>"exact","theme"=>"simple");
	
	public function init()
	{	
		$sourceDir = LIGHTWORX_PATH.'Vendors/Editor/tiny_mce/';
		$config = array(
			'source'=>$sourceDir,
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('tinyMCE',$config);
		$this->attachPackageScriptFile('tinyMCE','tiny_mce.js');
	}
	
	public function run()
	{
		if(!isset($this->editorProperties['elements']))
		{
			$this->editorProperties['elements'] = $this->editorId;
		}
		
		$scriptCode = 'tinyMCE.init({
			'.$this->getJQueryPluginProperties($this->editorProperties).'
		});';
		$this->addScriptCode($scriptCode);
	}
}