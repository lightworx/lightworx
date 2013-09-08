<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Tooltips;

use Lightworx\Foundation\Widget;

class Fancybox extends Widget
{
	public $obj;
	public $properties = array();
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/Dialog/fancybox/');
		self::publishResourcePackage('fancybox',$config);
		$this->attachPackageScriptFile('fancybox','jquery.mousewheel-3.0.4.pack.js');
		$this->attachPackageScriptFile('fancybox','jquery.fancybox-1.3.4.pack.js');
		$this->attachPackageCssFile('fancybox','jquery.fancybox-1.3.4.css');
	}
	
	public function run()
	{
		$this->addJqueryCode('$("'.$this->obj.'").fancybox({'.$this->prepareProperties().'});');
	}
	
	protected function prepareProperties()
	{
		return $this->getJQueryPluginProperties($this->properties);
	}
}