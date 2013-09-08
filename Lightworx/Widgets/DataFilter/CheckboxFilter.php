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
use Lightworx\Widgets\DataList\ListView;

class CheckboxFilter extends DataFilter
{

	public $delimiter = '.';
	
	public $filterStyleClass = 'checkbox_list_filter';
	public $attributeLabelStyleClass = 'checkbox_filter_label';
	public $template = "{attributeLabel}:{checkboxList}\n";
	public $requestEvent = 'click';
	public $displayAttributeLabel = true;
	public $checkboxListStyleClass = 'drop_down_list_filter';

	/**
	 * The checkbox items
	 *
	 * for example: 
	 * array(
	 *     array('name'=>'options[]','value'=>'1','label'=>'option1','checked'=>'checked');
	 *     array('name'=>'options[]','value'=>'2','label'=>'option2','checked'=>'');
	 * );
	 */
	public $checkboxListItems = array();
	
	public $replaceContainerIds = '#main';
	
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
		
		$url = $this->getApp()->request->getRequestURI();
		$this->action = strpos($url,'?')!==false ? $url.'&' : $url.'?';
		self::$filterValue[$this->attribute] = "$('".$this->getId(true)." .".$this->checkboxListStyleClass."').val()";
		
		$this->addJqueryCode("
		$('".$this->getId(true)." .".$this->checkboxListStyleClass."').live('".$this->requestEvent."',
			function ()
			{
				var url = '".$this->action."'+getAllFilterValues();
				var selector = '".$this->replaceContainerIds."';
				var replaceObjects = selector.split(',');
				var tempContainer = '#".$this->tempContainerId."';
				$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
			}
		);");
	}
	
	/**
	 * Return the checkbox
	 */
	public function getcheckboxList(array $items=array(),array $checkedItems=array())
	{
		$options = array();
		foreach($items as $text=>$option)
		{
			$option = array_merge(array('type'=>'checkbox','class'=>$this->checkboxListStyleClass),$option);
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
	
	public function renderCheckboxList()
	{
		$checkedItems = array();
		if(isset($_GET[$this->attribute]))
		{
			$checkedItems = explode($this->delimiter,$_GET[$this->attribute]);
		}
		return $this->getcheckboxList($this->checkboxListItems,$checkedItems);
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