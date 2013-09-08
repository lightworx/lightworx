<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Tooltips;

use Lightworx\Foundation\Widget;

class ResultNotify extends Widget
{
	public $customContainer = false;
	public $callbackFunction = 'afterFunction';
	
	public $resultNotifyOptions = array(
				'obj'=>'.result-box',
				'type'=>'success',
				'timeout'=>3000
			);
	
	public function init()
	{
		// loading mutipleload.js
		$jqueryConfig = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$jqueryConfig);
		$this->attachPackageScriptFile('jQuery','jquery.multipleload.js');

		// loading resutlNotify.js
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/Tooltips/ResultNotify/');
		self::publishResourcePackage('ResultNotify',$config);
		$this->attachPackageScriptFile('ResultNotify','jquery.resultNotify.js');
		$this->attachPackageCssFile('ResultNotify','result-notify.css');
		$this->initResultNotify();
	}
	
	public function initResultNotify()
	{
		$this->addJqueryCode('$.fn.resultNotify.create({'.$this->getJQueryPluginProperties($this->resultNotifyOptions).'});');
	}
	
	public function run()
	{
		if($this->customContainer===false and isset($this->resultNotifyOptions['obj']))
		{
			$objName = str_replace('.','',$this->resultNotifyOptions['obj']);
			echo '<div class="'.$objName.' alert result-notify-global">
					<button type="button" class="hidden" data-dismiss="alert">&times;</button>
				  	<h4 class="title"></h4>
					<p></p>
			</div>';
		}
	}
}