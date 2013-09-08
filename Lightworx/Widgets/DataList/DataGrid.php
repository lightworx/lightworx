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

use Lightworx\Queryworx\Tools\DataProvider;
use Lightworx\Widgets\DataList\ListView;
use Lightworx\Widgets\DataList\DataFilter;
use Lightworx\Widgets\DataList\DataGridOption;
use Lightworx\Helper\String;

class DataGrid extends ListView
{
	public $template = "{filters}\n{table}\n{option}\n{others}\n{pager}";
	public $theme = 'orange';
	public $dataGridStyleClass = 'orange';
	public $dataGridManageBarClassStyle = 'row_options';
	public $columns = array();
	public $optionView;

	public $dataGridAssetPath;

	public $themeFilePath;
	public $themeFile;
	public $formTagOptions = array('method'=>'post');
	public $otherView = array();
	public $deleteSelectedSubmitUrl;
	public $dataGridOrderStyleClass = 'data_grid_order_style';
	public $tempContainerId = "filter_temp_container";
	public $enableDataGridOption = true;
	
	/**
	 * Initialize data grid theme
	 */
	public function initializeGridTheme()
	{
		if($this->themeFilePath===null)
		{
			$this->themeFilePath = 'themes/';
		}

		$this->themeFile = $this->themeFilePath.$this->theme.'/css/blue.css';

		if($this->dataGridStyleClass!==null)
		{
			$this->themeFile = $this->themeFilePath.$this->theme.'/css/'.$this->dataGridStyleClass.'.css';
		}
	}
	
	public function initializeGridFields()
	{
		if(!is_array($this->columns))
		{
			throw new \RuntimeException("The property `columns` should be an array.");
		}

		$fields = $this->model->getPrimaryKeyName(true);

		if($this->columns===array())
		{
			foreach($this->model->getAttributes() as $column)
			{
				$this->columns[]['name'] = $column;
				$fields[] = $column;
			}
		}else{
			foreach($this->columns as $column)
			{
				if(isset($column['name']))
				{
					$fields[] = $column['name'];
				}
			}
		}

		if(!isset($this->criteriaParams['fields']))
		{
			$this->criteriaParams['fields'] = implode(",",array_unique($fields));
		}
	}
	
	/**
	 * Initialize widget DataGrid
	 */
	public function init()
	{
		$this->initializePage();
		$this->initializeDataOrder();
		$this->initializeDataCondition();
		
		$this->initializeGridTheme();
		$this->initializeGridFields();
		$this->initializeDataProvider();
		$this->initDataGridOption();
		$this->renderTemplates();

		if($this->dataGridAssetPath===null)
		{
			$this->dataGridAssetPath = LIGHTWORX_PATH.'Widgets/DataList/Assets/';
		}
		
		$config = array('source'=>$this->dataGridAssetPath);
		self::publishResourcePackage('DataGridAssets',$config);
		$this->attachPackageCssFile('DataGridAssets',$this->themeFile);
		
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
	}
	
	public function initDataGridOption()
	{
		if($this->optionView===null)
		{
			$dataGridOption = new DataGridOption($this);
			$this->optionView = $dataGridOption->run();
		}
	}
	
	public function run()
	{
		parent::run();
		$this->addJqueryCode("$('".$this->getId(true)." .".$this->dataGridOrderStyleClass."').live('click',function (){
				var url = $(this).attr('href');
				var selector = '".$this->getReplaceContainerIds()."';
				var replaceObjects = selector.split(',');
				var tempContainer = '#".$this->tempContainerId."';
				$.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
				return false;
		});");
	}
	
	/**
	 * Create the data grid head
	 */
	public function createGridHead()
	{
		$thead = array();
		$attributes = $this->model->attributeLabels();
		foreach($this->columns as $column)
		{
			$htmlOptions = isset($column['htmlOptions']) ? $this->getHtmlOptions($column['htmlOptions']) : '';
			if(isset($column['name']))
			{
				$label = isset($attributes[$column['name']]) ? $attributes[$column['name']] : self::getColumnName($column['name']);
				if(isset($column['enableSort']) and $column['enableSort']===false)
				{
					$thead[] = '<th'.$htmlOptions.'><span class="'.$this->dataGridOrderStyleClass.'">'.$label.'</span></th>';
				}else{
					$thead[] = '<th'.$htmlOptions.'>'.$this->getGridOrderByAttributeLink($column['name'],$label).'</th>';
				}
			}else{
				$thead[] = '<th'.$htmlOptions.'>'.$this->getDataColumn()->bindTableHead($column).'</th>';
			}
		}
		return '<thead class="title"><tr>'.implode('',$thead).'</tr></thead>';
	}
	
	/**
	 * Creates a column order link.
	 * @param string $attributeName
	 * @param string $attributeLabel
	 * @return string
	 */
	public function getGridOrderByAttributeLink($attributeName,$attributeLabel)
	{
		return '<a href="'.$this->createOrderUrl($attributeName).'" class="'.$this->dataGridOrderStyleClass.'">'.$attributeLabel.'</a>';
	}
	
	/**
	 * Create data grid body
	 * @return string
	 */
	public function createGridBody()
	{
		$tableRows = array();
		$recordSet = $this->dataProvider->getData($this->currentPage);
		foreach($recordSet as $model)
		{
			$tableRows[] = $this->getDataColumn()->createColumn($model);
		}
		return '<tbody>'.implode("\n",$tableRows).'</tbody>';
	}
	
	/**
	 * Display the data grid table.
	 * @return string
	 */
	public function renderTable()
	{
		$this->setReplaceContainerIds('table',$this->getId());
		return '<form'.$this->getFormTagOptions().'>
		<table id="'.$this->getId().'" class="'.$this->dataGridStyleClass.'">'.
		$this->createGridHead().
		$this->createGridBody().
		'</table></form>';
	}
	
	public function getFormTagOptions()
	{
		return $this->getHtmlOptions($this->formTagOptions);
	}
	
	/**
	 * Display others view
	 * @return string
	 */
	public function renderOthers()
	{
		return implode("\n",$this->otherView);
	}

	/**
	 * Display the option view
	 */
	public function renderOption()
	{
		return $this->optionView;
	}
	
	/**
	 * Returns a normalize column name
	 * @return string
	 */
	public static function getColumnName($name)
	{
		return str_replace('_',' ',$name);
	}
	
	public function getDataColumn()
	{
		return new DataGridColumn($this->columns,$this);
	}
}