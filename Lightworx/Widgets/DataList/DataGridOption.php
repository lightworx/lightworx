<?php

namespace Lightworx\Widgets\DataList;

use Lightworx\Foundation\Widget;
use Lightworx\Widgets\DataList\DataGrid;

class DataGridOption extends Widget
{
	public $dataGrid;

	public $deleteAction = ''; // the delete request URI.
	public $deleteMethod = 'post'; // supported method get or post.
	public $deleteCallbackFunction = 'function(){}';

	public function __construct(DataGrid $dataGrid)
	{
		parent::__construct();
		$this->dataGrid = $dataGrid;
	}

	public function getDataGridOptionContainerId()
	{
		return $this->getIdName().'_'.$this->dataGrid->getIdSequence();
	}

	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/jQuery/',
		);
		self::publishResourcePackage('jQuery',$config);
		
		$this->addJqueryCode("

			var DataGridId = '".$this->dataGrid->getId(true)."';
			// select all the rows.
			$(DataGridId+' .select_all').live('click',function(){
				if($(DataGridId+' .select_all').attr('checked')=='checked')
				{
					$(DataGridId+' input[class=data-grid-checkbox]').attr('checked','true');
				}else{
					$(DataGridId+' input[class=data-grid-checkbox]').removeAttr('checked');
				}
			});

			// select all rows
			$('#".$this->getDataGridOptionContainerId()." .select_all').live('click',function(){
				$(DataGridId+' input[class=data-grid-checkbox]').attr('checked','true');
				$(DataGridId+' .select_all').attr('checked','checked');
			});

			// cancel some one row, when cancelled a selected row, the checkbox .select_all should be change attribute checked to unchecked.
			$('input[class=data-grid-checkbox]').live('click',function(){
				if($(this).attr('checked')!='checked')
				{
					$(DataGridId+' input[name=select_all]').removeAttr('checked');
				}
			});

			// cancel all selected rows.
			$('#".$this->getDataGridOptionContainerId()." .cancel_selected').live('click',function(){
				$(DataGridId+' input[class=data-grid-checkbox]').removeAttr('checked');
				$(DataGridId+' .select_all').removeAttr('checked');
			});

			// toggle selected.
			$('#".$this->getDataGridOptionContainerId()." .toggle_selected').live('click',function(){
				var counter = 0;
				$(DataGridId+' input[class=data-grid-checkbox]').each(function(i,x){
					if($(x).attr('checked')=='checked')
					{
						counter++;
						$(x).removeAttr('checked');
					}else{
						$(x).attr('checked','checked');
					}
				});
				if(counter>0)
				{
					$(DataGridId+' .select_all').removeAttr('checked');
				}else{
					$(DataGridId+' .select_all').attr('checked','checked');
				}

			});

			$('#".$this->getDataGridOptionContainerId()." .delete_selected').live('click',function(){
				var counter = 0;
				$(DataGridId+' input[class=data-grid-checkbox]').each(function(i,x){
					if($(x).attr('checked')=='checked')
					{
						counter++;
					}
				});
				if(counter==0)
				{
					alert('".$this->__('You have not selected the records to be deleted')."');
				}
				if(counter>0 && confirm('".$this->__('Are you sure want to delete the selected record(s)?')."'))
				{
					$.".$this->deleteMethod."('".$this->deleteAction."',".$this->deleteCallbackFunction.");
				}
			});
		");
	}

	public function run()
	{
		if($this->dataGrid->enableDataGridOption===true)
		{
			$this->init();
			return '<div class="'.$this->dataGrid->dataGridManageBarClassStyle.'" id="'.$this->getDataGridOptionContainerId().'">
			<button type="button" class="select_all btn">'.$this->__('Check All').'</button>
			<button type="button" class="cancel_selected btn">'.$this->__('Uncheck All').'</button>
			<button type="button" class="toggle_selected btn">'.$this->__('Toggle All').'</button>
			<button type="button" class="delete_selected btn">'.$this->__('Delete checked').'</button>
			</div>';	
		}
	}
}