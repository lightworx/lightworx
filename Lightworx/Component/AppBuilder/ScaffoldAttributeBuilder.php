<?php

namespace Lightworx\Component\AppBuilder;

use Lightworx\Queryworx\Schema\TableSchema;
use Lightworx\Queryworx\Schema\ColumnSchema;

class ScaffoldAttributeBuilder
{

	public $formItemTemplate = '<div class="control-group">
			{label}
			<div class="controls">
				{item}
			</div>
	  	</div>
	  ';

	public function __construct($meta)
	{
		$this->meta = $meta;
	}

	public function generateFormItem()
	{
		$columns = $this->meta->columns;
		foreach($columns as $column)
		{
			if($column->isPrimaryKey===false)
			{
				$formType = $this->attributeFormMapping($column->dbType);
				$items['{label}'] = '<?php echo $form->label("'.$column->name.'",array("class"=>"control-label"));?>';
				$items['{item}'] = '<?php echo $form->'.$formType.'("'.$column->name.'",array("class"=>"required"));?>';
				$form[] = str_replace(array_keys($items),array_values($items),$this->formItemTemplate);
			}
		}
		return implode('',$form);
	}

	// generate attribute for view.
	public function generateAttributeItem()
	{
		$attributes = array();
		$columns = $this->meta->columns;
		foreach($columns as $column)
		{
			if($column->isPrimaryKey===false)
			{
				$attributes[] = '<p>'.$column->name.': <?php echo $model->'.$column->name.';?></p>'."\n";
			}
		}
		return implode('',$attributes);
	}

	public function attributeFormMapping($type)
	{
		$types = array(
			'int'=>'textInput',
			'varchar'=>'textInput',
			'char'=>'textInput',
			'text'=>'textArea',
			'tinyint'=>'textInput',
			'smallint'=>'textInput',
			'mediumint'=>'textInput',
			'int'=>'textInput',
			'bigint'=>'textInput',
			'decimal'=>'textInput',
			'float'=>'textInput',
			'double'=>'textInput',
			'real'=>'textInput',
			'bit'=>'textInput',
			'boolean'=>'textInput',
			'date'=>$this->pluginDatePicker(),
			'datetime'=>$this->pluginDatePicker(),
			'timestamp'=>$this->pluginDatePicker(),
			'time'=>'pluginTimePicker',
			'year'=>'pluginYearPicker',
			'char'=>'textInput',
			'varchar'=>'textInput',
			'tinytext'=>'textInput',
			'text'=>'textArea',
			'mediumtext'=>'textArea',
			'longtext'=>'textArea',
			'binary'=>'textArea',
			'varbinary'=>'textArea',
			'tinyblob'=>'textArea',
			'mediumblob'=>'textArea',
			'blob'=>'textArea',
			'longblob'=>'textArea',
			'enum'=>'dropDownList',
			'set'=>'checkboxList',
		);
		if(isset($types[$type]))
		{
			return $types[$type];
		}
		return 'textInput';
	}

	public function generateFormSubmitButton()
	{
		return '<?php $this->widget("Lightworx.Widgets.Request.SRFL",array("model"=>$model));?>
		<?php $this->widget("Lightworx.Widgets.Tooltips.ResultNotify");?>
		<div class="controls">
			<?php echo $form->submitButton("submit",array(
				"class"=>"btn srfl",
				"value"=>($model->getIsNewRecord() ? "Create" : "Update"),
				"srfl"=>"srfl",
				"after-function"=>"$.fn.resultNotify.complete(xhr,textStatus);",
				"request-success"=>"$.fn.resultNotify.show(\'submit success\',\'Result\');",
				"request-url"=>"/service/".get_class($model).($model->getIsNewRecord() ? "" : "?".$model->getPrimaryKeyName()."=".$model->{$model->getPrimaryKeyName()} ),
				"request-method"=>($model->getIsNewRecord() ? "post" : "put"),
				"serialize-form"=>"form[name=\'".get_class($model)."\']"
				));
			?>
		</div>';
	}

	public function pluginFileUploader()
	{

	}

	public function pluginDatepicker()
	{
		return 'getDatepicker';
	}
}

