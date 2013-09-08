<?php

namespace Lightworx\Widgets\Navigation;

use \Lightworx\Foundation\Widget;

class ActiveMenu extends Widget
{
	public $template = '<li[default]><a href="{url}" class="header_nav">{text}</a></li>';
	/**
	*  the struct based on your template code, it looks like that
	*	'template'=><li{active}><a href="{url}">{text}</a></li>
	*  'items'=>array(
	*	array('text'=>'Home','controller'=>'main','url'=>'/'),
	*	array('text'=>'Blog','controller'=>'blog','url'=>'/blog'),
	*	array('text'=>'Documentation','controller'=>'doc','url'=>'/documentation'),
	*	array('text'=>'Demo','controller'=>'demo','url'=>'/demo'),
	*	array('text'=>'Application','controller'=>'app','url'=>'/app'),
	*	array('text'=>'Tutorials','controller'=>'tutorial','url'=>'/tutorial'),
	*	array('text'=>'Plugins','controller'=>'plugin','url'=>'/plugin'),
	*	array('text'=>'Login','controller'=>'site','url'=>'/site/login','switch'=>Lightworx::getApplication()->user->isGuest()),
	*	array('text'=>'Logout','controller'=>'site','url'=>'/site/logout','switch'=>!Lightworx::getApplication()->user->isGuest()),
	*  )
	*/
	public $items = array();
	
	public $selectedItemStyleClassName = 'active';
	
	public $secureLogout = true;
	
	public function init()
	{
		if($this->secureLogout===true)
		{
			$this->addJqueryCode('$(".logout").live("click",function(){
				$.post($(this).attr("href"),function(){
					window.location.reload();
				});
				return false;
			});');
		}
	}
	
	/**
	 * Display the menu.
	 */
	public function run()
	{
		echo $this->renderMenu();
	}
	
	/**
	 * render menu to browser
	 * @return string
	 */
	public function renderMenu()
	{
		$menu = $placeholder = array();
		preg_match_all('~{(.+?)}~',$this->template,$placeholder);
		
		if(isset($placeholder[0]) and isset($placeholder[1]) and is_array($placeholder[0]) and is_array($placeholder[1]))
		{
			foreach($this->items as $item)
			{
				$template = $this->template;
				$function = "\Lightworx\Helper\ArrayHelper\iin_array";
				if($function($this->getRouter()->controller,$item['controller']))
				{
					if(isset($item['htmlOptions'],$item['htmlOptions']['class']))
					{
						$item['htmlOptions']['class'] .= ' '.$this->selectedItemStyleClassName;
					}else{
						$template = str_replace('[default]',' class="'.$this->selectedItemStyleClassName.'"',$template);
					}
				}
				$template = str_replace('[default]','',$template);
				
				foreach($item as $key=>$val)
				{
					if(is_array($val))
					{
						$val = $this->getHtmlOptions($val);
					}
					$template = str_replace('{'.$key.'}',$val,$template);
				}
				
				if(isset($item['switch']) and $item['switch']===false)
				{
					continue;
				}
				$menu[] = $template;
			}
			return implode("\n",$menu);
		}
	}
	
	public function getRouter()
	{
		return $this->getApp()->getRouter();
	}
}