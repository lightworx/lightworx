<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\DataList;

use Lightworx\Foundation\Widget;

abstract class BaseListView extends Widget
{
	public $template;
	
	// about filter
	public $filters = array();
	public $bindValues = array(); // the condition values
	public $replaceContainerIds = array();
	
	public $model;
	public $dataProvider;
	public $criteriaParams = array('fields'=>'*');
	
	abstract public function renderItems();
	
	public function __call($method,$value)
	{
		if(substr($method,0,3)=='get' and substr($method,-6)=='Filter')
		{
			$attribute = lcfirst(substr($method,3,-6));
			if(isset($this->filters[$attribute]))
			{
				return $this->getFilterClass($this->model,$attribute,$this->filters[$attribute])->createFilter();
			}
		}
	}
	
	public function getFilterClass($model,$attribute,$filter)
	{
		if(!isset($filter['filter']))
		{
			throw new \RuntimeException("The filter name have no set.");
		}
		$filterName = "\\Lightworx\\Widgets\\DataFilter\\".$filter['filter'];
		$filterInstance = new $filterName;
		$filterInstance->model = $model;
		$filterInstance->attribute = $attribute;
		$filterInstance->filters = $this->filters;
		$filterInstance->replaceContainerIds = $this->getReplaceContainerIds();
		foreach($filter as $property=>$value)
		{
			$filterInstance->{$property} = $value;
		}
		$filterInstance->init();
		return $filterInstance;
	}
	
	public function createDataFilters()
	{
		$filters = array();
		foreach($this->filters as $attribute=>$filter)
		{
			$filters[] = $this->getFilterClass($this->model,$attribute,$filter)->createFilter();
		}
		return $filters;
	}
	
	public function setReplaceContainerIds($object,$id)
	{
		$this->replaceContainerIds[] = '#'.$id;
	}
	
	public function getReplaceContainerIds($object='')
	{
		if($object!='')
		{
			return $this->replaceContainerIds[$object];
		}
		return implode(",",array_values($this->replaceContainerIds));
	}
}