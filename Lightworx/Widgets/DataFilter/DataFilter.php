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

use Lightworx\Foundation\Widget;
use Lightworx\Widgets\DataList\ListView;

abstract class DataFilter extends Widget
{
	public $action;
	public $model;
	public $filters;
	public $attribute;
	public $template;
	public $filterStyleClass;
	public $attributeLabelStyleClass = 'dataFilter';
	
	public static $filterRequestName = 'filter';
	public static $filterValue;
	public $tempContainerId = "filter_temp_container";

	abstract public function createFilterCondition(ListView $listView,$value);
	
	public function init(){}
	public function run(){}
	
	public function displayFilterItem()
	{
		$contents = $templates = array();
		preg_match_all('~{(\w+)}~',$this->template,$templates);
		foreach($templates[1] as $key=>$placeholder)
		{
			if(!isset($contents[$placeholder]))
			{
				$method = 'render'.ucfirst($placeholder);
				$contents[$placeholder] = method_exists($this,$method) ? $this->$method() : '';
			}
		}
		return str_replace($templates[0],$contents,$this->template);
	}
	
	/**
	 * Display the attribute label from the specified model.
	 * @return string
	 */
	public function renderAttributeLabel()
	{
		if($this->displayAttributeLabel===true)
		{
			$labels = $this->model->attributeLabels();
			$attribute = $this->attribute;
			if(isset($labels[$attribute]))
			{
				return $labels[$attribute];
			}
			return $attribute;
		}
	}
	
	public function createFilter()
	{
		return '<div id="'.$this->getId().'" class="'.$this->filterStyleClass.'">'.$this->displayFilterItem().'</div>';
	}
	
	
	public static function getFilterValue($class)
	{
		return isset(self::$filterValue[$class]) ? self::$filterValue[$class] : '';
	}
	
	public function __destruct()
	{
		$filterValues = array();
		foreach(self::$filterValue as $attribute=>$code)
		{
			$filterValues[] = '"&'.$attribute.'="+encodeURIComponent('.$code.')';
		}
		$jqueryFilterValues = implode('+',$filterValues);
		if(strpos($jqueryFilterValues,'"&')===0)
		{
			 $jqueryFilterValues = '"'.substr($jqueryFilterValues,2);
		}
		$this->addJqueryCode('
			function getAllFilterValues()
			{
				return '.$jqueryFilterValues.';
			}
		',false,'AllTheFilterValues');
	}
}