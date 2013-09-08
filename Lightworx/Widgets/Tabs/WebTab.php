<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Tabs;

use Lightworx\Foundation\Widget;

class WebTab extends Widget
{
	public $tabs;
	private $tabId;
	
	public $itemLabelStyleClass = 'item-label clearfix';
	public $itemCardStyleClass  = 'item-card clearfix';
	
	public $itemLabelsStyleClass = 'item-labels';
	public $itemCardsStyleClass  = 'item-cards';
	
	public function initStyle()
	{
		self::attachCssFile(__DIR__.'/Themes/purple.css');
	}
	
	public function initScript()
	{
		$this->addJqueryCode('
			$("#'.$this->tabId.' > .'.$this->itemCardsStyleClass.' >li").addClass("hidden");
			$("#'.$this->tabId.' > .'.$this->itemLabelsStyleClass.' >li").first().addClass("active");
			$("#'.$this->tabId.' > .'.$this->itemCardsStyleClass.' >li").first().addClass("active clearfix");
			$("#'.$this->tabId.' > .'.$this->itemLabelsStyleClass.' >li").live("click",function(){
				$("#'.$this->tabId.' > .'.$this->itemLabelsStyleClass.' >li").removeClass("active");
				$(this).addClass("active");
				$("#'.$this->tabId.' > .'.$this->itemCardsStyleClass.' >li").addClass("hidden").removeClass("active clearfix");
				$("#'.$this->tabId.' > .'.$this->itemCardsStyleClass.' >li#"+$(this).attr("for")).addClass("active clearfix").removeClass("hidden");
			});
		');
	}
	
	public function init()
	{
		$this->initStyle();
		$this->tabId = $this->getId();
		$this->initScript();
	}
	
	public function run()
	{
		$labels = $cards = array();
		
		if(isset($this->tabs['labels']) and is_array($this->tabs['labels']))
		{
			foreach($this->tabs['labels'] as $id=>$label)
			{
				$labels[] = '<li for="'.$id.'" class="'.$this->itemLabelStyleClass.'">'.$label.'</li>';
			}
		}
		
		if(isset($this->tabs['cards']) and is_array($this->tabs['cards']))
		{
			foreach($this->tabs['cards'] as $id=>$card)
			{
				$cards[] = '<li id="'.$id.'" class="'.$this->itemCardStyleClass.'">'.$card.'</li>';
			}
		}
		echo str_replace(array("{item-labels}","{item-cards}"),array(implode("\n",$labels),implode("\n",$cards)),$this->getTabTemplate());
	}
	
	public function getTabTemplate()
	{
		return '<div class="lightworx-widget-tabs clearfix" id="'.$this->tabId.'">
					<ul class="'.$this->itemLabelsStyleClass.'">{item-labels}</ul>
					<ul class="'.$this->itemCardsStyleClass.'">{item-cards}</ul>
				</div>';
	}
}