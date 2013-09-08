<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\DataFilter;

use Lightworx\Helper\Html;
use Lightworx\Widgets\Form\FormBuilder;
use Lightworx\Widgets\DataList\ListView;

class DropDownListFilter extends DataFilter
{
	public $filterStyleClass = 'drop_down_list_filter';
	public $attributeLabelStyleClass = 'drop_down_list_filter_label';
	public $template = "{attributeLabel}:{dropDownList}\n";
	public $requestEvent = 'change';
	public $displayAttributeLabel = true;
	public $dropDownListId = 'drop_down_list_filter';
	public $dropDownListItems = array();
	
	public $replaceContainerIds = '#main';
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
		
		// $this->addCssCode(".".$this->filterStyleClass."{width:auto;float:left;margin-left:5px;}");
		
		$url = $this->getApp()->request->getRequestURI();
		$this->action = strpos($url,'?')!==false ? $url.'&' : $url.'?';
		self::$filterValue[$this->attribute] = "$('".$this->getId(true)." #".$this->dropDownListId."').val()";
		
		// $this->addJqueryCode("
		// $('".$this->getId(true)." #".$this->dropDownListId."').live('".$this->requestEvent."',
		// 	function ()
		// 	{
		// 		var selector = '".$this->replaceContainerIds."';
		// 		var url = '".$this->action."'+getAllFilterValues();
		// 		var replaceObjects = selector.split(',');
		// 		var tempContainer = '#".$this->tempContainerId."';
		// 		$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
		// 	}
		// );");
		$this->addJqueryCode("
			$('".$this->getId(true)."').delegate('#".$this->dropDownListId."','".$this->requestEvent."',function(){
				var selector = '".$this->replaceContainerIds."';
				var url = '".$this->action."'+getAllFilterValues();
				var replaceObjects = selector.split(',');
				var tempContainer = '#".$this->tempContainerId."';
				$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
			});
		");
	}
	
	/**
	 * Parse the down list  items
	 */
	public function parseDownListOptions(array $items=array(),$selected='')
	{
		$options = array();
		foreach($items as $text=>$option)
		{
			if($selected!='' and isset($option['value']) and $option['value']==$selected)
			{
				$option['selected'] = "selected";
			}
			$options[] = Html::tag('option',$option['label'],$option);
		}
		return implode($options);
	}
	
	public function renderDropDownList()
	{
		$options = array('id'=>$this->dropDownListId,'name'=>$this->attribute);
		if(isset($_GET[$this->attribute]))
		{
			$defaultSelected = $_GET[$this->attribute];
			foreach($this->dropDownListItems as $attribute=>$params)
			{
				if((isset($params['selected']) and !isset($params['value'])) 
				   or
				  (isset($params['value']) and $params['value']!=$defaultSelected))
				{
					unset($this->dropDownListItems[$attribute]['selected']);
				}
				if(isset($params['value']) and $params['value']==$defaultSelected)
				{
					$this->dropDownListItems[$attribute]['selected'] = 'selected';
				}
			}
		}
		
		$items = $this->dropDownListItems;
		$optionTags = $this->parseDownListOptions($items,$this->attribute);
		return Html::tag('select',$optionTags,$options);
	}

	public function createFilterCondition(ListView $listView,$value)
	{
		if(trim($value)!=='')
		{
			$listView->bindValues[$this->attribute] = $value;
			return $this->attribute.' = :'.$this->attribute;
		}
	}
}