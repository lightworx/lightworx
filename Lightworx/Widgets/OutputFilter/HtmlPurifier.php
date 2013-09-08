<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\OutputFilter;

use Lightworx\Foundation\Widget;
use Lightworx\Foundation\ClassLoader;

class HtmlPurifier extends Widget
{
	public $options;

	public function init()
	{
		parent::beforeOutput();

		if(ClassLoader::hasNamespace('HtmlPurifier')===false)
		{
			ClassLoader::registerNamespace('HtmlPurifier',LIGHTWORX_PATH.'Vendors'.DS.'Security'.DS.'HtmlPurifier'.DS);
		}
	}

	public function run()
	{
		parent::afterOutput();
	}

	public function processOutput($output)
	{
		echo $this->purify($output);
	}
	
	public function purify($content)
	{
		$purifier=new \HtmlPurifier($this->options);
		$purifier->config->set('Cache.SerializerPath',$this->getApp()->getRuntimePath());
		return $purifier->purify($content);
	}
}