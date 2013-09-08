<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link https://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Calendar;

use Lightworx\Foundation\Widget;

class Datepicker extends Widget
{
	public $selector;
	public $datepickerProperties = array('format'=>'yyyy-mm-dd');

	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Bootstrap/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('Bootstrap',$config);
		$this->attachPackageScriptFile("Bootstrap",'datepicker/js/bootstrap-datepicker.js');
		$this->attachPackageCssFile("Bootstrap",'datepicker/css/datepicker.css');
	}

	public function run()
	{
		$this->addJqueryCode(
			'$("'.$this->selector.'").datepicker({
				'.$this->getJQueryPluginProperties($this->datepickerProperties).'
			}).on("click",function(){
				$(this).datepicker("show");
			}).on("changeDate",function(){
				$(this).datepicker("hide");
			});'
		);
	}
}