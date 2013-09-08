<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link https://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Highlight;

use Lightworx\Foundation\Widget;

class GoogleCodePrettify extends Widget
{
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Highlight/GoogleCodePrettify/',
		);
		self::publishResourcePackage('GoogleCodePrettify',$config);
		$this->attachPackageScriptFile("GoogleCodePrettify","prettify.js");
		$this->attachPackageCssFile("GoogleCodePrettify","prettify.css");
	}
	
	public function run()
	{
		$this->addJqueryCode('
			$("pre").addClass("prettyprint linenums");
			window.prettyPrint && prettyPrint();
		');
	}
}