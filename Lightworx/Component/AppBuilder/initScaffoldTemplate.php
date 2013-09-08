<?php

return array(
	'controllers/{controllerName}.php'=>'<?php

use Lightworx\Exception\HttpException;

class {controllerName} extends Controller
{
	protected $_model;

	public function {actionPrefix}{UCF_defaultAction}()
	{
		$this->render("{defaultAction}");
	}

	public function {actionPrefix}{UCF_createAction}()
	{
		$this->render("{createAction}");
	}

	public function {actionPrefix}{UCF_updateAction}()
	{
		$model = $this->loadModel();
		$this->render("{updateAction}",array("model"=>$model));
	}

	public function {actionPrefix}{UCF_viewAction}()
	{
		$model = $this->loadModel();
		$this->render("{viewAction}",array("model"=>$model));
	}

	protected function loadModel($insert=false)
	{
		if($this->_model===null and $insert===false)
		{
			$this->loadBaseModel(new {modelName});
		}

		if($insert===true)
		{
			return new {modelName};
		}
		return $this->_model;
	}
}',

	'models/{modelName}.php'=>'<?php

class {modelName} extends ActiveRecord
{
	protected $_tableName = "{tableName}";

	static public function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function rules()
	{
		return {method.code:rules};
	}

	public function defaultScope()
	{
		return array();
	}

	protected function beforeSave()
	{
		return parent::beforeSave();
	}

	protected function afterSave()
	{
		parent::afterSave();
	}

	public function attributeLabels()
	{
		return {method.code:attributeLabels};
	}
}',
	'views/{controllerPath}/{defaultAction}.php'=>'<?php $this->widget("Lightworx.Widgets.Tooltips.ResultNotify");?>
<h3>{modelName} List</h3>
<div style="float:right;margin:0px 5px 10px 0px;">
	<a class="btn" href="{controllerPath}/create">Add {modelName}</a>
</div>
<div class="data-grid-container">
<?php
$model = new {modelName};
$this->widget("Lightworx.Widgets.Request.SRFL",array("model"=>$model));
$this->widget("Lightworx.Widgets.DataList.DataGrid",array(
	"theme"=>"cyanine",
	"dataGridStyleClass"=>"cyanine",
	"template" => "{filters}\n{table}\n{option}\n{others}\n{pager}",
	"model"=>$model,
	"pageUrlTemplate"=>"/{controllerPath}?page={page:d+}",
	"enableDataGridOption"=>false,
	"paginatorConfig"=>array(
		"enableAjaxPage"=>true
	),
	"pageSize"=>20,
	"columns"=>array(
	),
));
?>
</div>',
	'views/{controllerPath}/{createAction}.php'=>'<?php echo $this->renderPartial("{controllerPath}/_form",array("model"=>new {modelName}));?>',
	'views/{controllerPath}/{updateAction}.php'=>'<?php echo $this->renderPartial("{controllerPath}/_form",array("model"=>$model));?>',
	'views/{controllerPath}/{viewAction}.php'=>'{view.code:attribute_items}',
	'views/{controllerPath}/_form.php'=>'<?php $form = $this->widget("Lightworx.Widgets.Form.RichFormBuilder",array("model"=>$model));?>
<?php echo $form->beginForm($_SERVER["REQUEST_URI"],"post",array("class"=>"form-horizontal","name"=>"{modelName}"));?>

	<div class="control-group">
		<div class="controls" style="margin-top:20px;">
			<?php if($model->getIsNewRecord()===true):?>
				<h3>Add {modelName}</h3>
			<?php else:?>
				<h3>Update {modelName}</h3>
			<?php endif;?>
		</div>
  	</div>
	
	<?php echo $form->errorSummary();?>
	{view.code:_form_item}
	<?php echo $form->endForm();?>
',
);