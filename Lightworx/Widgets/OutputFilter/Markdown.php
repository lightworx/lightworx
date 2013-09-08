<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\OutputFilter;

use Lightworx\Foundation\ClassLoader;
use Lightworx\Foundation\Widget;
use Lightworx\Widgets\OutputFilter\HtmlPurifier;
use Michelf\Markdown as BaseMarkdown;

class Markdown extends Widget
{
	public $purifyOutput = true;

	public function init()
	{
		parent::beforeOutput();
		if(ClassLoader::hasNamespace('Michelf')===false)
		{
			ClassLoader::registerNamespace('Michelf',LIGHTWORX_PATH.'Vendors'.DS.'Filter'.DS.'Markdown'.DS);
		}
	}
	
	public function run()
	{
		parent::afterOutput();
	}
	
	public function transform($output)
	{
		return BaseMarkdown::defaultTransform($output);
	}
	
	public function processOutput($output)
	{
		$output=$this->transform($output);
		if($this->purifyOutput)
		{
			$purifier= new HtmlPurifier;
			$purifier->init();
			$output=$purifier->purify($output);
		}
		echo $output;
	}
}