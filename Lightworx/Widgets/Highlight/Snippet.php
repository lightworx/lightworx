<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link http://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Highlight;

use Lightworx\Foundation\Widget;

class Snippet extends Widget
{
	public $codeContainer;
	public $codeLanguage = 'php';
	public $snippetProperties;
	
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Highlight/snippet/',
		);
		self::publishResourcePackage('snippet',$config);
		$this->attachPackageScriptFile("snippet","jquery.snippet.min.js");
		$this->attachPackageCssFile("snippet","jquery.snippet.min.css");
	}
	
	public function run()
	{
		if($this->codeContainer!==null)
		{
			$this->addJqueryCode('
				$("'.$this->codeContainer.'").snippet("'.$this->codeLanguage.'",
				{
					'.$this->getJQueryPluginProperties($this->snippetProperties).'
				});
				
				$("'.$this->codeContainer.'").css("-moz-border-radius","5px");
				$("'.$this->codeContainer.'").css("-webkit-border-radius","5px");
				$("'.$this->codeContainer.'").css("moz-border-radius","5px");
			');
		}
	}
}