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

class TextSearchFilter extends DataFilter
{
	public $filterStyleClass = 'text_search_filter';
	public $attributeLabelStyleClass = 'text_search_filter_label';
	public $template = "{attributeLabel}:{textSearchInput}\n{searchButton}";
	public $requestEvent = 'blur';
	public $enableSearchButton = false;
	public $displayAttributeLabel = true;
	public $textSearchInputId = 'text_search_filter_input';
	public $textSearchInputOptions = array('type'=>'search','x-webkit-speech'=>'x-webkit-speech');
	public $searchButtonId = 'text_search_filter_button';
	public $searchButtonOptions = array('type'=>'button','class'=>'filter_search_button');
	
	/**
	 * The like matching pattern, The following options are supported:
	 * left,right,full
	 * @var string
	 */
	public $matchPattern = 'full';
	
	/**
	 * When the new data is loaded, which objects wanna to replace should be specified.
	 * @var string
	 */
	public $replaceContainerIds = '#main';
	
	public function init()
	{
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
		// $this->addCssCode(".".$this->filterStyleClass."{width:auto;display:block;margin-left:5px;}");
		
		$url = $this->getApp()->request->getRequestURI();
		$this->action = strpos($url,'?')!==false ? $url.'&' : $url.'?';
		self::$filterValue[$this->attribute] = "$('".$this->getId(true)." #".$this->textSearchInputId."').val()";

		$this->addJqueryCode("
			function ".$this->getId()."TextSearchFilter_load()
			{
				var selector = '".$this->replaceContainerIds."';
				var url = '".$this->action."'+getAllFilterValues();
				var replaceObjects = selector.split(',');
				var tempContainer = '#".$this->tempContainerId."';
				$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
			}
		$('".$this->getId(true)." #".$this->searchButtonId."').live('click',".$this->getId()."TextSearchFilter_load);
		$('".$this->getId(true)." #".$this->textSearchInputId."').live('".$this->requestEvent."',".$this->getId()."TextSearchFilter_load);
		");
	}

	public function renderTextSearchInput()
	{
		$inputOptions = array('id'=>$this->textSearchInputId,'name'=>$this->attribute);
		if(isset($_GET[$this->attribute]))
		{
			$inputOptions['value'] = $_GET[$this->attribute];
		}
		$inputOptions = array_merge($inputOptions,$this->textSearchInputOptions);
		return Html::tag('input','',$inputOptions,false);
	}
	
	public function renderSearchButton()
	{
		if($this->enableSearchButton===true)
		{
			$inputOptions = array('id'=>$this->searchButtonId,'value'=>$this->__('Search'));
			$inputOptions = array_merge($inputOptions,$this->searchButtonOptions);
			return Html::tag('button',$this->__('Search'),$inputOptions);
		}
	}
	
	public function createFilterCondition(ListView $listView,$value)
	{
		if(trim($value)!=='')
		{
			$listView->bindValues[$this->attribute] = $value;
			if($this->matchPattern=='full')
			{
				$listView->bindValues[$this->attribute] = '%'.$value.'%';
			}
			if($this->matchPattern=='left')
			{	
				$listView->bindValues[$this->attribute] = $value.'%';
			}
			if($this->matchPattern=='right')
			{
				$listView->bindValues[$this->attribute] = '%'.$value;
			}
			return $this->attribute.' like :'.$this->attribute;
		}
	}
}