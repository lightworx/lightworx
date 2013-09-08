<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\PageLoader;

use Lightworx\Foundation\Widget;

/**
 * The widget Lazyload dependency jQuery plugin lazyload, that for lazy loading the image files.
 * you can using '$properties' to assign property for the widget.
 * @see http://www.appelsiini.net/projects/lazyload
 * The image tag should be like following:
 * @example <img src="img/grey.gif" data-original="img/real_image_file.jpg" width="765" height="574" />
 * @version $Id$
 * @author Stephen Lee <stephen.lee@lightworx.io>
 */
class Lazyload extends Widget
{
	public $properties = array("effect"=>"fadeIn");
	public $selector = '$("img")';
	
	public function init()
	{
		$this->addCssCode('.lazy{display: none;}');
		$this->attachScriptFile(__DIR__."/jquery.lazyload.min.js");
	}
	
	public function run()
	{
		$this->addJqueryCode($this->selector.'.lazyload({'.$this->getProperties().'});');
	}
	
	/**
	 * Return a property string.
	 * @return string
	 */
	public function getProperties()
	{
		$code = array();
		foreach($this->properties as $property=>$value)
		{
			$code[] = $property.':'.'"'.$value.'"';
		}
		return implode(",",$code);
	}
}