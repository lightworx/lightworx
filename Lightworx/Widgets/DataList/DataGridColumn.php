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
use Lightworx\Helper\Html;

class DataGridColumn extends Widget
{
	public $columns;
	public static $rowStyle = array('row1','row2');
	
	protected $dataGrid;
	
	public function init(){}
	public function run(){}
	
	public function __construct(array $columns,DataGrid $dataGrid)
	{
		$this->columns = $columns;
		$this->dataGrid = $dataGrid;
		$this->language = $dataGrid->language;
	}
	
	public function createColumn($model)
	{
		$pk = $model->getPrimaryKeyName();
		$rowId = lcfirst(get_class($model)).'_'.$model->{$pk};
		$tableColumns = array();
		foreach($this->columns as $column)
		{
			$contents = '';
			foreach($column as $method=>$params)
			{
				$methodName = 'create'.ucfirst($method);
				if(method_exists($this,$methodName))
				{
					$contents = $this->{$methodName}($model,$column);
					break;
				}
			}
			$htmlOptions = isset($column['htmlOptions']) ? $this->getHtmlOptions($column['htmlOptions']) : '';
			$tableColumns[] = '<td'.$htmlOptions.'>'.$contents.'</td>';
		}
		return '<tr class="'.self::getRowStyle().'" id="'.$rowId.'">'.implode("\n",$tableColumns).'</tr>';
	}
	
	protected function createName($model,array $column=array())
	{
		$model->{$column['name']} = $this->encodeAttributeValue($model->{$column['name']},$column);

		if(isset($column['value']))
		{
			$value = $this->evaluateExpression($column['value'],array('model'=>$model));
		}else{
			$value = $model->{$column['name']};
		}
		return $value;
	}

	public function encodeAttributeValue($value,$column)
	{
		if(!isset($column['encode']))
		{
			return Html::encode($value);
		}else{
			if(is_callable($column['encode']))
			{
				$method = $column['encode'];
				return $method($value);
			}
			throw new \RuntimeException('Cannot to invoke the encode method.');
		}
	}
	
	static public function getRowStyle()
	{
		if(($rowStyle = next(self::$rowStyle))!==false)
		{
			return $rowStyle;
		}
		reset(self::$rowStyle);
		return self::$rowStyle[0];
	}
	
	protected function createManageColumn($model,array $buttons=array(),array $urlParams=array())
	{
		$params = array_merge($model->getPrimaryKey(true),$urlParams);
		return $this->createManageOptions($model,$buttons['manageColumn'],$params);
	}
	
	/**
	 * Create the manage button
	 */
	protected function createManageOptions($model,array $buttons=array(),array $params=array())
	{
		$buttonLabels = array();
		if($buttons===array())
		{
			$confirmTip = $this->__('Are you sure want to delete it?');
			$buttons = array(
				'view'=>array('action'=>'view'),
				'update'=>array('action'=>'update'),
				'delete'=>array('action'=>'delete','htmlOptions'=>array('onclick'=>'return confirm(\''.$confirmTip.'\');')),
			);
			
			foreach($buttons as $type=>$button)
			{
				$htmlOptions = isset($button['htmlOptions']) ? $button['htmlOptions'] : array();
				$htmlOptions['href'] = $this->createManageUrl($button,$params);
				$buttonLabels[] = Html::tag('a',$this->getManageIcon($type),$htmlOptions);
			}
		}else{
			foreach($buttons as $type=>$button)
			{
				if(is_string($button))
				{
					$buttonLabels[$type] = $this->evaluateExpression($button,array('model'=>$model));
				}
			}
		}
		return implode("\n",$buttonLabels);
	}

	/**
	 * Get the data manage icon
	 * @param string $type the icon type.
	 * @return string
	 */
	public function getManageIcon($type)
	{
		$types = array('view'=>'globe','update'=>'edit','delete'=>'trash');
		if(isset($types[$type]))
		{
			return '<i class="icon-'.$types[$type].'" data-original-title="'.$this->__($type).'" style="margin:0 7px;"></i>';
		}
	}
	
	/**
	 * Get the button image label
	 * @param string $type the button type.
	 * @return string
	 */
	public function getButtonImage($type)
	{
		$path = $this->getPackagePublishPath('DataGridAssets').'themes/'.$this->dataGrid->theme;
		return Html::tag('img','',array('src'=>$path.'/images/'.$type.'.png'),false);
	}
	
	/**
	 * Creates a the manage url
	 * @param array $config
	 * @param array $params
	 * @return string
	 */
	public function createManageUrl(array $config,array $params=array())
	{
		if(isset($config['baseUrl']))
		{
			$url = $config['baseUrl'];
		}else{
			if(!isset($config['action']))
			{
				throw new \RuntimeException("Have no specified the action.");
			}
			
			if(!isset($config['controller']))
			{
				$controller = $this->getApp()->getRouter()->controller;
			}else{
				$controller = $config['controller'];
			}
			$url = DS.$controller.DS.$config['action'];	
		}
		return $this->getApp()->getRouter()->createUrl($url,$params);
	}
	
	public function createCheckboxColumn($model=null)
	{
		if($model===null)
		{
			$id = get_class($this);
		}else{
			$id = $model->{$model->getPrimaryKeyName()};
		}
		return '<input type="checkbox" class="data-grid-checkbox" name="data_row_checkbox[]" value="'.$id.'" />';
	}
	
	/**
	 * Creates a head of the table.
	 * @param array $column
	 * @return string
	 */
	public function bindTableHead(array $column)
	{
		$method = current(array_keys($column));
		$methodName = 'create'.ucfirst($method).'TableHead';
		if(method_exists($this,$methodName))
		{
			return $this->{$methodName}();
		}
		return;
	}
	
	/**
	 * Set the checkbox column head title
	 */
	public function createCheckboxColumnTableHead()
	{
		$this->addJqueryCode('$("input[class=data-grid-checkbox]").live("click",function(){
			$(this).parent().parent().toggleClass("selected");
		});
		$("input[name=select_all]").live("click",function(){
			$("table.'.$this->dataGrid->theme.' tbody tr").each(function(i,x){
				$(x).toggleClass("selected");
			});
		});');
		return '<input type="checkbox" name="select_all" class="data-grid-checkbox select_all" />';
	}
	
	/**
	 * Set the checkbox column head title
	 */
	public function createManageColumnTableHead()
	{
		return $this->__('Manage');
	}
}