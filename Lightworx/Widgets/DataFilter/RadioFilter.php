<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\DataFilter;

use Lightworx\Helper\Html;
use Lightworx\Widgets\DataList\ListView;

class RadioFilter extends DataFilter
{
	
	public $filterStyleClass = 'radio_list_filter';
	public $attributeLabelStyleClass = 'radio_filter_label';
	public $template = "{attributeLabel}:{radioList}\n";
	public $requestEvent = 'click';
	public $displayAttributeLabel = true;
	public $radioListStyleClass = 'radio_list_filter';

	/**
	 * The radio items
	 *
	 * for example: 
	 * array(
	 *     array('name'=>'options[]','value'=>'1','label'=>'option1','checked'=>'checked');
	 *     array('name'=>'options[]','value'=>'2','label'=>'option2','checked'=>'');
	 * );
	 */
	public $radioListItems = array();
	
	public $replaceContainerIds = '#main';
	
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
		
		$url = $this->getApp()->request->getRequestURI();
		$this->action = strpos($url,'?')!==false ? $url.'&' : $url.'?';
		self::$filterValue[$this->attribute] = "$('".$this->getId(true)." .".$this->radioListStyleClass."').val()";
		
		$this->addJqueryCode("
		$('".$this->getId(true)." .".$this->radioListStyleClass."').live('".$this->requestEvent."',
			function ()
			{
				var selector = '".$this->replaceContainerIds."';
				var url = '".$this->action."'+getAllFilterValues();
				var replaceObjects = selector.split(',');
				var tempContainer = '#".$this->tempContainerId."';
				$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
			}
		);");
	}
	
	/**
	 * Return the radio items
	 */
	public function getradioList(array $items=array(),array $checkedItems=array())
	{
		$options = array();
		foreach($items as $text=>$option)
		{
			$option = array_merge(array('type'=>'radio','class'=>$this->radioListStyleClass),$option);
			if(isset($option['value']) and in_array($option['value'],$checkedItems))
			{
				$option['checked'] = "checked";
			}
			
			if(!isset($option['label']))
			{
				$option['label'] = '';
			}
			
			$options[] = Html::tag('label',Html::tag('input','',$option,false).$option['label']);
		}
		return implode($options);
	}
	
	public function renderRadioList()
	{
		$checkedItems = array();
		if(isset($_GET[$this->attribute]))
		{
			$checkedItems[] = $_GET[$this->attribute];
		}
		return $this->getradioList($this->radioListItems,$checkedItems);
	}

	public function createFilterCondition(ListView $listView,$value)
	{
		if(trim($value)!=='')
		{
			$listView->bindValues[$this->attribute] = '%'.$value.'%';
			return $this->attribute.' like :'.$this->attribute;
		}
	}
}