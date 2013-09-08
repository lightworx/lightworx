<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Navigation;

use Lightworx\Foundation\Widget;

class Scroll2Top extends Widget
{
	public $template = '<div id="{id}" style="{cssCode}">{scrollButton}</div>';
	
	public $scrollButton;
	
	/**
	 * The anchor is specified which DOM anchor scroll to.
	 * defaults to '0', meaning scroll to the page top, and that supports to set string or integer.
	 * @var mixed
	 */
	public $anchor = 0;
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Widgets/Navigation/Scroll2Top/');
		self::publishResourcePackage('Scroll2Top',$config);
		$this->scrollButton = '<img src="'.self::getPackagePublishPath('Scroll2Top').'scroll2top.png"/>';
		
		$this->cssCode = 'display:none;position: fixed; bottom: 120px; right: 100px; cursor: pointer;';
		$params = array('{id}'=>$this->getId(),'{cssCode}'=>$this->cssCode,'{scrollButton}'=>$this->scrollButton);
		foreach($params as $key=>$val)
		{
			$this->template = str_replace($key,$val,$this->template);
		}
		
		
		// initialize the scroll jquery code
		$jqueryAnchorCode = "var pos = 0;";
		if(!is_int($this->anchor))
		{
			$jqueryAnchorCode = "var pos = $('".$this->anchor."').offset().top;\n";
		}
		
		$this->addJqueryCode('
			$("'.$this->getId(true).'").live("click",function(){
				'.$jqueryAnchorCode.'
				$("html,body").animate({scrollTop: pos}, "10");
			});
			$(window).scroll(function(){
				if($(this).scrollTop()>500)
				{
					$("'.$this->getId(true).'").fadeIn("slow");
				}else{
					$("'.$this->getId(true).'").fadeOut("slow");
				}
			});');
	}

	public function run()
	{
		echo $this->template;
	}
}
?>