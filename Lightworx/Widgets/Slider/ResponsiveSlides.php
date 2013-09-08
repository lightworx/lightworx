<?php

namespace Lightworx\Widgets\Slider;

use Lightworx\Foundation\Widget;

class ResponsiveSlides extends Widget
{
	
	public $sliderName = '.rslides';
	
	public $sliderProperties = array(
		"auto"=>"true",
		"nav"=>"true",
		"speed"=>"800",
		"timeout"=>"6000",
		"namespace"=>"rslides",
	);
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/Slider/ResponsiveSlides/');
		self::publishResourcePackage('ResponsiveSlides',$config);
		$this->attachPackageCssFile("ResponsiveSlides",'responsiveslides.css');
		$this->attachPackageCssFile("ResponsiveSlides",'themes.css');
		$this->attachPackageScriptFile("ResponsiveSlides",'responsiveslides.min.js');
	}
	
	public function run()
	{
		$this->addScriptCode('$("'.$this->sliderName.'").responsiveSlides({
			'.$this->getJQueryPluginProperties($this->sliderProperties).'
		});');
	}
}