<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link http://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Tooltips;

use Lightworx\Foundation\Widget;

/**
 * The widget Poshytip dependency vendor jquery plugin poshytip.
 * @version $Id$
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @since 0.1
 * @example $tip = $this->widget("Lightworx.Widgets.Tooltips.Poshytip");
 * 			// create custom tip
 *			$tip->setClassName("tip-violet")->setBgImageFrameSize(9)->createTip("tip");
 * 			// create a Skyblue tip on the specified DOM id:#tip
 *			$tip->tipSkyblue("tip");
 *			// change the was already exist tip property
 *			$tip->setAlignY("bottom")->tipTwitter("tip");
 * 			// set multiple DOM id.
 * 			$tip->setAlignY("bottom")->tipTwitter(array("tip","tips"));
 *			$tip->tipYellowSimpleRC('doc_search');
 *			$tip->tipYellowSimpleLC('doc_search');
 *			$tip->tipYellowSimpleCB('doc_search');
 *			$tip->tipYellowSimpleTC('doc_search');
 *          // set the DOM class to apply a tip function.
 *			$tip->tipTwitter(".className");
 *			echo '<a id="tip" title="hello" href="#">test</a> | <a id="tips" title="hello" href="#">test</a>';
 */
class Poshytip extends Widget
{
	public $styleProperties = array();
	
	public $styles = array(
		"basic"=>array(),
		"violet"=>array(
			"className"=>"tip-violet",
			"bgImageFrameSize"=>9
		),
		"darkgray"=>array(
			"className"=>"tip-darkgray",
			"bgImageFrameSize"=>11,
			"offsetX"=>-25
		),
		"skyblue"=>array(
			"className"=>"tip-skyblue",
			"bgImageFrameSize"=>9,
			"offsetX"=>0,
			"offsetY"=>20
		),
		"yellowsimple"=>array(
			"className"=>"tip-yellowsimple",
			"showTimeout"=>1,
			"alignTo"=>"target",
			"alignX"=>"center",
			"offsetY"=> 5,
			"allowTipHover"=>"false"
		),
		"twitter"=>array(
			"className"=>"tip-twitter",
			"showTimeout"=>1,
			"alignTo"=>"target",
			"alignX"=>"center",
			"offsetY"=>5,
			"allowTipHover"=>"false",
			"fade"=>"false",
			"slide"=>"false"
		),
		"green"=>array(
			"className"=>"tip-green",
			"offsetX"=>-7,
			"offsetY"=>16,
			"allowTipHover"=>"false"
		),
		"yellowSimpleRC"=>array(
			"className"=>"tip-yellowsimple",
			"showOn"=>"focus",
			"alignTo"=>"target",
			"alignX"=>"right",
			"alignY"=>"center",
			"offsetX"=>5
		),
		"yellowSimpleLC"=>array(
			"className"=>"tip-yellowsimple",
			"showOn"=>"focus",
			"alignTo"=>"target",
			"alignX"=>"left",
			"alignY"=>"center",
			"offsetX"=>5
		),
		"yellowSimpleCB"=>array(
			"className"=>"tip-yellowsimple",
			"showOn"=>"focus",
			"alignTo"=>"target",
			"alignX"=>"center",
			"alignY"=>"bottom",
			"offsetX"=>0,
			"offsetY"=>5
		),
		"yellowSimpleTC"=>array(
			"className"=>"tip-yellowsimple",
			"showOn"=>"focus",
			"alignTo"=>"target",
			"alignX"=>"center",
			"alignY"=>"top",
			"offsetX"=>0,
			"offsetY"=>5
		),
		"yellowSimpleILC"=>array(
			"className"=>"tip-yellowsimple",
			"showOn"=>"focus",
			"alignTo"=>"target",
			"alignX"=>"inner-left",
			"alignY"=>"center",
			"offsetX"=>0,
			"offsetY"=>5
		),
	);
	
	public $cssFiles = array(
		"tip-yellow"=>"tip-yellow/tip-yellow.css",
		"tip-violet"=>"tip-violet/tip-violet.css",
		"tip-darkgray"=>"tip-darkgray/tip-darkgray.css",
		"tip-skyblue"=>"tip-skyblue/tip-skyblue.css",
		"tip-yellowsimple"=>"tip-yellowsimple/tip-yellowsimple.css",
		"tip-twitter"=>"tip-twitter/tip-twitter.css",
		"tip-green"=>"tip-green/tip-green.css"
	);
	
	protected $tipTheme;
	
	public function init()
	{
		$cssFiles = array(
			"tip-yellow/tip-yellow.css",
			"tip-violet/tip-violet.css",
			"tip-darkgray/tip-darkgray.css",
			"tip-skyblue/tip-skyblue.css",
			"tip-yellowsimple/tip-yellowsimple.css",
			"tip-twitter/tip-twitter.css",
			"tip-green/tip-green.css"
		);
		
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Tooltips/poshytip/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		$this->addCssCode("th, td, caption {padding:0px;}");
		self::publishResourcePackage('poshytip',$config);
		$this->attachPackageScriptFile('poshytip','jquery.poshytip.min.js');
		$this->attachPackageCssFiles('poshytip',$cssFiles);
	}
	
	public function run(){}
	
	public function __destruct()
	{
		if(isset($this->cssFiles[$this->tipTheme]))
		{
			$this->attachPackageCssFile('poshytip',$this->cssFiles[$this->tipTheme]);
		}
	}
	
	public function addTipCode($id,array $tipConfig=array())
	{
		$this->tipTheme = $tipConfig['className'];
		$propertyCode = array();
		foreach($tipConfig as $property=>$value)
		{
			$value = is_string($value) ? "'".$value."'" : $value;
			$propertyCode[] = $property.':'.$value;
		}
		if(isset($id[0]) and $id[0]==='.')
		{
			$code = '$("'.$id.'").poshytip({'.implode(",",$propertyCode).'});';
		}else{
			$code = '$("#'.$id.'").poshytip({'.implode(",",$propertyCode).'});';
		}
		$this->styleProperties = array(); // initialize the styleProperties again.
		$this->addJqueryCode($code);
	}
	
	public function setStyleProperty($proeprty,$config)
	{
		$this->styleProperties[$proeprty] = $config;
	}
	
	/**
	 * Loading specified style
	 */
	public function __call($method,$value)
	{
		if(substr($method,0,3)=='set')
		{
			$this->setStyleProperty(lcfirst(substr($method,3)),$value[0]);
			return $this;
		}
		
		if(substr($method,0,3)=='tip')
		{
			$id = lcfirst(substr($method,3));
			if(isset($this->styles[$id]))
			{
				if(is_array($value[0]))
				{
					foreach($value[0] as $domId)
					{
						$this->addTipCode($domId,array_merge($this->styles[$id],$this->styleProperties));
					}
				}else{
					$this->addTipCode($value[0],array_merge($this->styles[$id],$this->styleProperties));
				}
			}else{
				throw new \RuntimeException("The style ".$id." have no define.");
			}
		}
	}
	
	/**
	 * Create a tip
	 * @param string $id
	 */
	public function createTip($id)
	{
		$this->addTipCode($id,$this->styleProperties);
	}
}