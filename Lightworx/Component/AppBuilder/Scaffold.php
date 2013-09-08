<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\AppBuilder;

use Lightworx\Queryworx\Schema\TableSchema;
use Lightworx\Queryworx\Schema\ColumnSchema;

class Scaffold
{
	public $name;
	public $appPath;
	public $placeholders = array(
		'{actionPrefix}'=>'action',
		'{UCF_createAction}'=>'Create',
		'{UCF_updateAction}'=>'Update',
		'{UCF_viewAction}'=>'View',
		'{createAction}'=>'create',
		'{updateAction}'=>'update',
		'{viewAction}'=>'view',
	);

	static protected $_rules = array();
	static public $numberTypes = array(
			"int",
			"tinyint",
			"smallint",
			"mediumint",
			"bigint",
			"date",
			"datetime",
			"year",
			"timestamp",
			"time",
			"float",
			"double",
	);
	static protected $models = array();

	public function __construct($name,$appPath=null)
	{
		$this->name = ucfirst($name);
		$this->appPath = $appPath;
		
		$this->createScaffold();
	}

	public function getAppPath()
	{
		if($this->appPath===null or trim($this->appPath)=='')
		{
			return $_SERVER['PWD'].'/';
		}
		if(substr($this->appPath, 0,1)!='/')
		{
			if(trim($this->appPath)=='.')
			{
				return $_SERVER['PWD'].'/';
			}
			return $_SERVER['PWD'].'/'.$this->appPath;
		}
		return $this->appPath;
	}
	
	public function createScaffold()
	{
		$templates = $this->getTemplate();
		$this->getTemplatePlaceholders();
		$this->getMethodPlaceholders();
		
		if($templates!==false)
		{
			foreach($templates as $file=>$template)
			{
				$this->generateFile($file,$template);
			}
			return true;
		}
		throw new \RuntimeException('Cannot get the templates.');
	}

	public function generateFile($file,$template)
	{
		if($this->placeholders!==array() and is_array($this->placeholders))
		{
			$file = str_replace(array_keys($this->placeholders),array_values($this->placeholders),$file);
			$template = str_replace(array_keys($this->placeholders),array_values($this->placeholders),$template);
			$file = $this->getAppPath().$file;
			if(file_exists($file))
			{
				throw new \RuntimeException('The file: '.$file.' already exists.');
			}
			if(!file_exists(dirname($file)))
			{
				mkdir(dirname($file),0777,true);
			}
			file_put_contents($file, $template);
		}
	}

	public function getTemplatePlaceholders()
	{
		$placeholders = array(
			'{controllerName}'=>$this->getControllerName(),
			'{UCF_defaultAction}'=>ucfirst($this->getDefaultAction()),
			'{defaultAction}'=>$this->getDefaultAction(),
			'{modelName}'=>ucfirst($this->getModelName()),
			'{tableName}'=>$this->getTableName(),
			'{controllerPath}'=>lcfirst($this->getModelName()),
		);
		$this->placeholders = array_merge($this->placeholders,$placeholders);
	}

	protected function getMethodPlaceholders()
	{
		$placeholders = array(
			'{method.code:rules}'=>$this->generateModelRules(),
			'{method.code:attributeLabels}'=>$this->generateModelLabels(),
			'{view.code:_form_item}'=>$this->generateFormCode(),
			'{view.code:attribute_items}'=>$this->generateAttributeItems(),
		);
		$this->placeholders = array_merge($this->placeholders,$placeholders);
	}

	protected function getModelName()
	{
		return $this->name;
	}

	protected function getTableName()
	{
		include_once(LIGHTWORX_PATH.'Vendors/String/Inflector.php');
		return lcfirst(\Inflector::pluralize($this->name));
	}

	protected function getTemplate()
	{
		$templates = include(__DIR__.'/initScaffoldTemplate.php');
		if(is_array($templates))
		{
			return $templates;
		}
		return false;
	}

	public function getControllerName()
	{
		$controllerExt = \Lightworx::getApplication()->controllerExtension;
		return ucfirst($this->name).$controllerExt;
	}

	public function getDefaultAction()
	{
		return \Lightworx::getApplication()->defaultAction;
	}

	protected function generateModelRules()
	{
		$rules = 'array()';
		$meta = $this->getMeta();
		
		if(($meta instanceof TableSchema) and is_array($meta->columns))
		{
			foreach($meta->columns as $attribute=>$options)
			{
				$this->resolveAttributeRules($attribute,$options);
			}
			$rules = "array(\n".$this->exportRules()."\t\t)";
		}

		return $rules;
	}

	protected function exportRules()
	{
		if(!is_array(self::$_rules))
		{
			return 'array()';
		}

		$rules = '';
		foreach(self::$_rules as $validator=>$attributes)
		{
			if($validator=='required')
			{
				$rules .= "\t\t\t".'array("'.implode(', ',$attributes).'","'.$validator.'"),'."\n";
			}
			
			if($validator=='number')
			{
				$rules .= "\t\t\t".'array("'.implode(', ',$attributes).'","'.$validator.'"),'."\n";
			}

			if($validator=='range')
			{
				if(is_array($attributes))
				{
					foreach($attributes as $rangeAttribute=>$range)
					{
						$rules .= "\t\t\t".'array("'.$rangeAttribute.'","'.$validator.'","range"=>array("'.implode('","',$range['range']).'")),'."\n";
					}
				}
			}

			if($validator=='length' and isset($attributes['max']))
			{
				if(is_array($attributes))
				{
					foreach($attributes as $langthAttribute=>$length)
					{
						$rules .= "\t\t\t".'array("'.$langthAttribute.'","'.$validator.'","max"=>'.$length['max'].'),'."\n";
					}
				}
			}
		}
		return $rules;
	}

	protected function resolveAttributeRules($attribute,ColumnSchema $options)
	{
		if($options->isPrimaryKey===true)
		{
			return true;
		}

		// required
		if($options->allowNull===false and $options->defaultValue===null)
		{
			self::$_rules['required'][] = $attribute;
		}

		// number
		if(in_array($options->dbType,self::$numberTypes))
		{
			self::$_rules['number'][] = $attribute;
		}

		// length
		if(is_int($options->limit) and $options->limit>0)
		{
			self::$_rules['length'][$attribute] = array('max'=>$options->limit);
		}

		// range
		if(is_array($options->extra) and $options->extra!==array())
		{
			self::$_rules['range'][$attribute] = array('range'=>$options->extra);
		}
	}

	protected function generateModelLabels()
	{
		$labels = array();
		$meta = $this->getMeta();
		foreach($meta->columns as $attribute=>$options)
		{
			$label = implode(" ",array_map('ucfirst',explode("_",$attribute)));
			$labels[] = "\t\t\t".'"'.$attribute.'"=>"'.$label.'",'."\n";
		}
		return "array(\n".implode('',$labels)."\t\t)";
	}

	public function generateAttributeItems()
	{
		$sf = new ScaffoldAttributeBuilder($this->getMeta());
		return $sf->generateAttributeItem();
	}

	protected function generateFormCode()
	{
		$sf = new ScaffoldAttributeBuilder($this->getMeta());
		return $sf->generateFormItem().$sf->generateFormSubmitButton();
	}

	protected function getMeta()
	{
		$modelName = ucfirst($this->getModelName());
		if(!isset(self::$models[$modelName]))
		{
			eval('class '.$modelName.' extends ActiveRecord{protected $_tableName = "'.$this->getTableName().'";}');
			$model = new $modelName;
			self::$models[$modelName] = $model->getMetaData();
		}
		return self::$models[$modelName];
	}
}