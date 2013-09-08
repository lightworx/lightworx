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

class WYSIHtml5 extends Widget
{
	public $editorId;
	public $editorProperties = array("mode"=>"exact","theme"=>"simple");
	
	public function init()
	{	
		$sourceDir = LIGHTWORX_PATH.'Vendors/Bootstrap/wysihtml5editor/';
		$config = array(
			'source'=>$sourceDir,
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('Bootstrap',$config);
		$this->attachPackageCssFile('Bootstrap','src/css/bootstrap.min.css');
		$this->attachPackageCssFile('Bootstrap','wysihtml5editor/src/bootstrap-wysihtml5.css');
		$this->attachPackageScriptFile('Bootstrap','wysihtml5editor/lib/js/wysihtml5-0.3.0.min.js');
		$this->attachPackageScriptFile('Bootstrap','src/js/bootstrap.min.js');
		$this->attachPackageScriptFile('Bootstrap','wysihtml5editor/src/bootstrap-wysihtml5.js');
	}
	
	public function run()
	{
		$scriptCode = '$("#'.$this->editorId.'").wysihtml5({
			'.$this->getJQueryPluginProperties($this->editorProperties).'
		});';
		$this->addScriptCode($scriptCode);
	}
}